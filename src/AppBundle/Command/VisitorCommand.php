<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VisitorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('visitor:location')
            ->setDescription('Get location')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->getContainer()->get('doctrine')
            ->getRepository('AppBundle:Visitor');

        $query = $repository->createQueryBuilder('v')
            ->select('v.id, v.ip')
            ->where('v.location IS NULL')
            ->orderBy('v.id', 'DESC')
            ->getQuery();

        $visitors = $query->setMaxResults(5)->getResult();

        $qUpdate = $repository->createQueryBuilder('v')
            ->update()
            ->set('v.location', ':location')
            ->where('v.id = :id')
            ->getQuery();

        $qDelete = $repository->createQueryBuilder('v')
            ->delete()
            ->where('v.id = :id')
            ->getQuery();

        $filterLocation = '/(Lviv|Kiev)/';

        foreach ($visitors as $visitor) {
            $json = file_get_contents('http://ip-api.com/json/' . $visitor['ip']);
            $result = json_decode($json, true);
            $city = null;

            if (!array_key_exists('status', $result)) {
                continue;
            } else if ($result['status'] == 'success') {
                $city = $result['city'];
                $location = $result['city'] . ', ' . $result['regionName'];
            } else if ($result['status'] == 'fail') {
                $location = $result['message'];
            } else {
                $location = $json;
            }

            if (!is_null($city) && preg_match($filterLocation, $city)) {
                $qDelete
                    ->setParameter('id', $visitor['id'])
                    ->execute();
            } else {
                $qUpdate
                    ->setParameter('location', $location)
                    ->setParameter('id', $visitor['id'])
                    ->execute();
            }
        }

        $output->writeln(count($visitors));
    }
}
