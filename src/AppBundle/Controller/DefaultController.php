<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity\Email;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', []);
    }

    /**
     * @Route("/order", name="order")
     */
    public function orderAction(Request $request)
    {
        // replace this example code with whatever you need
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
        $em = $this->getDoctrine()->getManager();
        $em->persist($email);
        $em->flush();

        return new JsonResponse([
            'status' => 1
        ]);
    }
}
