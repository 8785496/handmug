<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\Email;

class SendMailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('send:mail')
            ->setDescription('Send mail from database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->getContainer()->get('doctrine')
            ->getRepository('AppBundle:Email');

        $query = $repository->createQueryBuilder('m')
            //->select('m.id,  m.status')
            ->where('m.status IS NULL')
            ->orderBy('m.id', 'DESC')
            ->getQuery();

        $mails = $query->setMaxResults(1)->getResult();

        $qUpdate = $repository->createQueryBuilder('m')
            ->update()
            ->set('m.status', ':status')
            ->where('m.id = :id')
            ->getQuery();

        $mailer = $this->getContainer()->get('mailer');

        foreach ($mails as $mail) {
            $body = 'Имя: ' . $mail->getName() . "\n"
                . 'Email: ' . $mail->getEmail() . "\n"
                . 'Сообщение: ' . $mail->getBody();

            $message = \Swift_Message::newInstance()
                ->setSubject($mail->getSubject())
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
                ->setParameter('id', $mail->getId())
                ->execute();
        }

        $output->writeln(count($mailer));
    }
}
