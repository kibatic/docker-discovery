<?php

require __DIR__ . '/vendor/autoload.php';

use Kibatic\Command\DockerDiscoveryCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new DockerDiscoveryCommand());

$application->run();
