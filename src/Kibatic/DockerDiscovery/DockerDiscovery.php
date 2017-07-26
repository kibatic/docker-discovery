<?php

namespace Kibatic\DockerDiscovery;

use Docker\Docker;

class DockerDiscovery
{
    const FORMAT_ARRAY = 1;
    const FORMAT_JSON = 2;

    public function discover($validImageNames = [], $format = self::FORMAT_ARRAY)
    {
        if (!in_array($format, [self::FORMAT_ARRAY, self::FORMAT_JSON])) {
            throw new \Exception('Unsupported format');
        }

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

        if ($format === self::FORMAT_JSON) {
            return json_encode($results);
        }

        return $results;
    }
}
