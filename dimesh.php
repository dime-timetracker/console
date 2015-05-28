<?php
require 'vendor/autoload.php';
if (!($argc === 3 and $argv[1] === 'activities' and $argv[2] === 'show')) {
    echo 'Sorry, for now we have only the command "activities and the subcommand "show", so please type "php dimesh.php activities show"' . PHP_EOL;
} else {
    $console = new DimeConsole\DimeConsole();
    $console->readConfig();
    $console->login();
    $console->showActivities();
}

