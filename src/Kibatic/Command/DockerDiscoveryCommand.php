<?php

namespace Kibatic\Command;

use Docker\Docker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DockerDiscoveryCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('docker-discovery')
            ->addArgument(
                'patterns',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Image name pattern you want to match (separated by spaces). Ex: \'mariadb:.+\', \'mysql:.+\''
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validImageNames = $input->getArgument('patterns');

        $docker = new Docker();

        $containers = $docker->getContainerManager()->findAll();

        $filter = function ($container, $validImageNames) {
            foreach ($validImageNames as $validImageName) {
                if (preg_match('/' . $validImageName . '/', $container->getImage())) {
                    return true;
                }
            }

            return false;
        };

        $results = [];

        foreach ($containers as $container) {
            if (!$filter($container, $validImageNames)) {
                continue;
            }

            $containerName = str_replace('/', '', $container->getNames()[0]);

            $network = (array) $container->getNetworkSettings()->getNetworks();
            $ipAddress = array_shift($network)->getIpAddress();

            $results[] = [
                'name' => $containerName,
                'ip' => $ipAddress,
                'network' => $network
            ];
        }

        echo json_encode($results) . "\n";
    }
}
