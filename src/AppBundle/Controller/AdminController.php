<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     */
    public function indexAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Visitor');
        $query = $repository->createQueryBuilder('v')
            ->where('v.time >= :date')
            ->setParameter('date', new \DateTime('today'))
            ->orderBy('v.id', 'DESC')
            ->getQuery();

        $visitors = $query->getResult();

        return $this->render('admin/index.html.twig', [
            'caption' => 'Посетители сегодня',
            'visitors' => $visitors
        ]);
    }

    /**
     * @Route("/admin/yesterday", name="yesterday")
     */
    public function yesterdayAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Visitor');
        $query = $repository->createQueryBuilder('v')
            ->where('v.time >= :yesterday AND v.time < :today')
            ->setParameter('today', new \DateTime('today'))
            ->setParameter('yesterday', new \DateTime('yesterday'))
            ->orderBy('v.id', 'DESC')
            ->getQuery();

        $visitors = $query->getResult();

        return $this->render('admin/index.html.twig', [
            'caption' => 'Посетители вчера',
            'visitors' => $visitors
        ]);
    }

    /**
     * @Route("/admin/order", name="adminOrder")
     */
    public function orderAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Request');
        $query = $repository->createQueryBuilder('o')
            ->orderBy('o.id', 'DESC')
            ->getQuery();

        $orders = $query->getResult();

        return $this->render('admin/order.html.twig', [
            'caption' => 'Заявки с сайта',
            'orders' => $orders
        ]);
    }

    /**
     * @Route("/admin/mail", name="adminMail")
     */
    public function mailAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Email');
        $query = $repository->createQueryBuilder('m')
            ->orderBy('m.id', 'DESC')
            ->getQuery();

        $mails = $query->getResult();

        return $this->render('admin/mail.html.twig', [
            'caption' => 'Письма с сайта',
            'mails' => $mails
        ]);
    }

    public function menuAction($uri, $ip) {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Email');
        $query = $repository->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery();
        $countMail = $query->getSingleScalarResult();

        $repositoryOrder = $this->getDoctrine()->getRepository('AppBundle:Request');
        $queryOrder = $repositoryOrder->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->getQuery();
        $countOrder = $queryOrder->getSingleScalarResult();

        return $this->render('admin/menu.html.twig', [
            'countMail' => $countMail,
            'countOrder' => $countOrder,
            'uri' => $uri,
            'ip' => $ip
        ]);
    }
}
