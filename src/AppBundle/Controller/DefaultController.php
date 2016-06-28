<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Email;
use AppBundle\Entity\Visitor;
use AppBundle\Entity\Request as Order;

class DefaultController extends Controller
{
    /**
     * @Route("/", host="{city}.handmug.ru")
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request, $city = null)
    {
        $this->saveVisitor($request);

        return $this->render('default/index.html.twig', [
            'city' => $city
        ]);
    }

    /**
     * @Route("/order/{model}", defaults={"model" = ""}, name="order")
     */
    public function orderAction(Request $request, $model, $city = null)
    {
        $this->saveVisitor($request);

        return $this->render('default/order.html.twig', [
            'model' => urldecode($model),
            'city' => $city
        ]);
    }

    /**
     * @Route("/send_order", name="sendOrder")
     * @Method("POST")
     */
    public function sendOrderAction(Request $request)
    {
        $order = (new Order())
            ->setName($request->request->get('name'))
            ->setContact($request->request->get('contact'))
            ->setModel($request->request->get('model'))
            ->setType($request->request->get('type'))
            ->setDescription($request->request->get('description'))
            ->setIp($request->getClientIp())
            ->setTime(new \DateTime());

        $validator = $this->get('validator');
        $errors = $validator->validate($order);
        if (count($errors) > 0) {
            return new JsonResponse([
                'status' => 0,
                'error' => $errors[0]->getMessage()
            ]);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        return new JsonResponse(['status' => 1]);
    }

    /**
     * @Route("/send_mail", name="sendMail")
     * @Method("POST")
     */
    public function sendMailAction(Request $request)
    {
        $email = (new Email())
            ->setEmail($request->request->get('email'))
            ->setName($request->request->get('name'))
            ->setSubject('Email from site Handmug.ru')
            ->setBody($request->request->get('message'))
            ->setTime(new \DateTime())
            ->setIp($request->getClientIp());

        $validator = $this->get('validator');
        $errors = $validator->validate($email);
        if (count($errors) > 0) {
            return new JsonResponse([
                'status' => 0,
                'error' => $errors[0]->getMessage()
            ]);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($email);
        $em->flush();

        return new JsonResponse(['status' => 1]);
    }

    /**
     * @Route("/counter", name="counter")
     */
    public function counterAction(Request $request)
    {
        $uri = $request->get('uri');
        $this->saveVisitor($request, $uri);
        $fileName = __DIR__ . "/../Resources/counter.png";
        return new BinaryFileResponse($fileName, 200, [
            'cache-control' => 'no-cache, no-store, must-revalidate',
            'pragma' => 'no-cache'
        ]);
    }

    private function saveVisitor(Request $request, $uri = null) {
        $filterAgent = '/(google|yandex|bing)/i';
        $userAgent = $request->headers->get('User-Agent');
        if (preg_match($filterAgent, $userAgent)) {
            return;
        }

        if (is_null($uri)) {
            $uri = $request->getUri();
        }
        $visitor = (new Visitor())
            ->setIp($request->getClientIp())
            ->setUri(urldecode($uri))
            ->setAgent($userAgent)
            ->setReferer($request->headers->get('referer'))
            ->setTime(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($visitor);
        $em->flush();
    }
}
