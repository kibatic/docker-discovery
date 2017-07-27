<?php

namespace Kibatic\DockerDiscovery;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DockerDiscoveryCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('docker-discovery')
            ->addArgument(
                'filter',
                InputArgument::REQUIRED,
                'On what information to filter containers. (possible: image, name, label)'
            )
            ->addArgument(
                'regexps',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Image name pattern you want to match (separated by spaces). Ex: \'mariadb:.+\' \'mysql:.+\''
            )
            ->addOption(
                'twig',
                null,
                InputOption::VALUE_REQUIRED
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filter = $input->getArgument('filter');
        $regexps = $input->getArgument('regexps');

        $dockerDiscovery = new DockerDiscovery();

        $containers = $dockerDiscovery->discover($filter, $regexps);

        $template = $input->getOption('twig');

        if ($template) {
            $loader = new \Twig_Loader_Filesystem(getcwd());
            $twig = new \Twig_Environment($loader);
            $rendered = $twig->render($template, ['containers' => $containers]);

            $output->writeln($rendered);
            return;
        }

        $output->writeln(json_encode($containers, JSON_PRETTY_PRINT));
    }
}
