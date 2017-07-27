<?php

namespace Kibatic\DockerDiscovery;

class Container
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $image;
    /**
     * @var array
     */
    public $ipAddresses;
    /**
     * @var array
     */
    public $environment;
    /**
     * @var array
     */
    public $labels;
    /**
     * @var \Docker\API\Model\Container
     */
    public $full;
}
