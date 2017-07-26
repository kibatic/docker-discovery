<?php

namespace Kibatic\DockerDiscovery;

use Docker\Docker;

class DockerDiscovery
{
    public function discover($validImageNames = [])
    {
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

        return json_encode($results);
    }
}
