<?php

namespace DimeConsole;

use Symfony\Component\Console\Application;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionHandler;
use Stecman\Component\Symfony\Console\BashCompletion\Completion;

class DimeShellCompletionCommand extends CompletionCommand
{
    protected  function configureCompletion(CompletionHandler $handler)
    {
        $taskCompletion = new Completion(
            'activities',
            'task',
            Completion::TYPE_ARGUMENT,
            [
                'show',
                'resume',
                'stop',
                'interactive'
            ]
        );

        $idCompletion = new Completion(
            'activities',
            'id',
            Completion::TYPE_OPTION,
            function() {
                $client = new DimeClient();
                return $client->requestActivityIds();
            }
        );

        $nameCompletion = new Completion(
            'activities',
            'name',
            Completion::TYPE_OPTION,
            function() {
                $client = new DimeClient();
                return array_keys($client->requestActivityNames());
            }
        );

        $handler->addHandlers([$taskCompletion, $idCompletion, $nameCompletion]);
    }
}
