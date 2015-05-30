<?php

require __DIR__.'/vendor/autoload.php';

use DimeConsole\ActivitiesCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new ActivitiesCommand());
$application->run();