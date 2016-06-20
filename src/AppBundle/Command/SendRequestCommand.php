<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SendRequestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('send:request')
            ->setDescription('Send order from database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->getContainer()->get('doctrine')
            ->getRepository('AppBundle:Request');

        $query = $repository->createQueryBuilder('o')
            ->where('o.status IS NULL')
            ->orderBy('o.id', 'DESC')
            ->getQuery();

        $orders = $query->setMaxResults(1)->getResult();

        $qUpdate = $repository->createQueryBuilder('o')
            ->update()
            ->set('o.status', ':status')
            ->where('o.id = :id')
            ->getQuery();

        $mailer = $this->getContainer()->get('mailer');

        foreach ($orders as $order) {
            $body = 'Имя: ' . $order->getName() . "\n"
                . 'Контакт: ' . $order->getContact() . "\n"
                . 'Модель: ' . $order->getModel() . "\n"
                . 'Тип: ' . $order->getType() . "\n"
                . 'Описание: ' . $order->getDescription();

            $message = \Swift_Message::newInstance()
                ->setSubject('Order from site handmug.ru')
                ->setFrom('mail@handmug.ru')
                ->setTo('mail@handmug.ru')
                ->setBody($body);

            if ($mailer->send($message)) {
                $status = 1;
            } else {
                $status = -1;
            }

            $qUpdate
                ->setParameter('status', $status)
                ->setParameter('id', $order->getId())
                ->execute();
        }

        $output->writeln(count($orders));
    }

}
