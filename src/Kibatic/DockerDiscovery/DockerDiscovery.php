<?php

namespace Kibatic\DockerDiscovery;

use Docker\Docker;
use Docker\API\Model\Container as FullContainer;

class DockerDiscovery
{
    const FILTER_IMAGE = 'image';
    const FILTER_NAME = 'name';
    const FILTER_LABEL = 'label';

    public function discover($filterName, $filterRegexps = [])
    {
        if (!in_array($filterName, [
            self::FILTER_IMAGE,
            self::FILTER_NAME,
            self::FILTER_LABEL
        ])) {
            throw new \Exception('The filter "' . $filterName . '" does not exist');
        }

        $docker = new Docker();

        $containerInfos = $docker->getContainerManager()->findAll();
        /**
         * @var FullContainer[] $fullContainers
         */
        $fullContainers = [];

        foreach ($containerInfos as $k => $containerInfo) {
            $fullContainers[] = $docker->getContainerManager()->find($containerInfo->getId());
        }

        $filters = [
            self::FILTER_IMAGE => function (Container $container, $regexps) {
                foreach ($regexps as $regexp) {
                    if (preg_match('/' . $regexp . '/', $container->image)) {
                        return true;
                    }
                }

                return false;
            },
            self::FILTER_NAME => function (Container $container, $regexps) {
                foreach ($regexps as $regexp) {
                    if (preg_match('/' . $regexp . '/', $container->name)) {
                        return true;
                    }
                }

                return false;
            },
            self::FILTER_LABEL => function (Container $container, $regexps) {
                foreach ($regexps as $regexp) {
                    foreach ($container->labels as $labelName => $labelValue) {
                        if (preg_match('/' . $regexp . '/', $labelName)) {
                            return true;
                        }
                    }
                }

                return false;
            }
        ];

        $containers = [];

        foreach ($fullContainers as $k => $fullContainer) {
            $container = $this->createContainer($fullContainer);

            if (!$filters[$filterName]($container, $filterRegexps)) {
                continue;
            }

            $containers[] = $container;
        }

        return $containers;
    }

    private function createContainer(FullContainer $fullContainer)
    {
        $container = new Container();
        $container->name = str_replace('/', '', $fullContainer->getName());
        $container->image = $fullContainer->getConfig()->getImage();

        $environment = [];

        foreach ($fullContainer->getConfig()->getEnv() as $env) {
            $env = explode('=', $env);
            $environment[$env[0]] = $env[1];
        }

        $container->environment = $environment;

        $container->labels = $fullContainer->getConfig()->getLabels();

        $ipAddresses = [];

        foreach ($fullContainer->getNetworkSettings()->getNetworks() as $network) {
            $ipAddresses[] = $network->getIPAddress();
        }

        $container->ipAddresses = $ipAddresses;
        $container->full = $fullContainer;

        return $container;
    }
}
