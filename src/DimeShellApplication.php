<?php

namespace DimeConsole;

use Symfony\Component\Console\Application;

class DimeShellApplication extends Application
{
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new DimeShellCompletionCommand();
        return $commands;
    }
}
