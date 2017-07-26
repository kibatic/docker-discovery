<?php

namespace Kibatic\DockerDiscovery;

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
                'Image name pattern you want to match (separated by spaces). Ex: \'mariadb:.+\' \'mysql:.+\''
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validImageNames = $input->getArgument('patterns');

        $dockerDiscovery = new DockerDiscovery();

        $output->writeln($dockerDiscovery->discover($validImageNames, DockerDiscovery::FORMAT_JSON));
    }
}
