<?php

namespace DimeConsole;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class ActivitiesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('activities')
            ->setDescription('Does something with activitities')
            ->addArgument(
                'task',
                InputArgument::REQUIRED,
                'What do you want to do with your activities?'
            )
       ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('task');
        if ($name === 'show') {
            $console = new DimeConsole();
            $console->readConfig();
            $console->login();
            $result = $console->showActivities();
            $console->logout();

            $table = new Table($output);
            $table
                ->setHeaders(array('Id', 'Description'))
                ->setRows($result);
            $table->render();
        } else {
            $output->writeln('<error>Sorry, for now we have only the command "activities and the subcommand "show", so please type "php dimesh.php activities show"</error>');
        }
    }
}
