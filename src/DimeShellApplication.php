<?php

namespace DimeConsole;

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class DimeShellApplication
 * @package DimeConsole
 * @author Thomas Jez
 *
 * provides tab completion and a service container in addition to the standard Symfony console application class
 */
class DimeShellApplication extends Application
{
    private $services;

    /**
     * 
     */
    function __construct()
    {
        parent::__construct();
        $this->registerServices();
    }

    /**
     * @return ContainerBuilder
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @return array|\Symfony\Component\Console\Command\Command[]
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new DimeShellCompletionCommand();
        return $commands;
    }

    /**
     *
     */
    private function registerServices()
    {
        $this->services = new ContainerBuilder();
        $loader = new YamlFileLoader($this->services, new FileLocator(dirname(__DIR__)));
        $loader->load('services.yml');    }
}
