<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Email;
use AppBundle\Entity\Visitor;

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
     * @Route("/order", name="order")
     */
    public function orderAction(Request $request)
    {
        $this->saveVisitor($request);

        return $this->render('default/order.html.twig', []);
    }

    /**
     * @Route("/send_order", name="sendOrder")
     * @Method("POST")
     */
    public function sendOrderAction(Request $request)
    {
        return new JsonResponse([
            'data' => 123
        ]);
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
        $email->setTime(new \DateTime());
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

    private function saveVisitor(Request $request) {
        $visitor = new Visitor();
        $visitor->setIp($request->getClientIp());
        $visitor->setUri($request->getUri());
        $visitor->setAgent($request->headers->get('User-Agent'));
        $visitor->setReferer($request->headers->get('referer'));
        $visitor->setTime(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($visitor);
        $em->flush();
    }
}
