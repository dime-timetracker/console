<?php

namespace DimeConsole;

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DimeShellApplication extends Application
{
    private $services;

    function __construct()
    {
        parent::__construct();
        $this->registerServices();
    }

    public function getServices()
    {
        return $this->services;
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new DimeShellCompletionCommand();
        return $commands;
    }

    private function registerServices()
    {
        $this->services = new ContainerBuilder();
        $this->services->register('client', '\DimeConsole\DimeClient');
    }
}
