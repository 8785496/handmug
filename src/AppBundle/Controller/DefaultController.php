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
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $this->saveVisitor($request);

        return $this->render('default/index.html.twig', []);
    }

    /**
     * @Route("/order/{model}", defaults={"model" = ""}, name="order")
     */
    public function orderAction(Request $request, $model)
    {
        $this->saveVisitor($request);

        return $this->render('default/order.html.twig', [
            'model' => urldecode($model)
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
            ->setTime(new \DateTime("now",new \DateTimeZone("Asia/Novosibirsk")));

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
        $email = new Email();
        $email->setEmail($request->request->get('email'));
        $email->setName($request->request->get('name'));
        $email->setSubject('Email from site Handmug.ru');
        $email->setBody($request->request->get('message'));
        $email->setTime(new \DateTime("now",new \DateTimeZone("Asia/Novosibirsk")));
        $email->setIp($request->getClientIp());

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
        $this->saveVisitor($request);
        $fileName = __DIR__ . "/../Resources/counter.png";
        return new BinaryFileResponse($fileName);
    }

    private function saveVisitor(Request $request) {
        $visitor = new Visitor();
        $visitor->setIp($request->getClientIp());
        $visitor->setUri(urldecode($request->getUri()));
        $visitor->setAgent($request->headers->get('User-Agent'));
        $visitor->setReferer($request->headers->get('referer'));
        $visitor->setTime(new \DateTime("now",new \DateTimeZone("Asia/Novosibirsk")));

        $em = $this->getDoctrine()->getManager();
        $em->persist($visitor);
        $em->flush();
    }
}
