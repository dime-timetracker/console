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
    protected $client;

    protected function configure()
    {
        $this
            ->setName('activities')
            ->setDescription('Does something with activitities')
            ->addArgument(
                'task',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'What do you want to do with your activities?'
            )
       ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->client = new DimeClient();
        $task = $input->getArgument('task');
        if ($task[0] === 'show') {
            $this->showActivities($output);
        } elseif ($task[0] === 'resume') {
            $this->resumeActivity($output, $task);
        } elseif ($task[0] === 'stop') {
            $this->stopActivity($output, $task);
        } else {
            $output->writeln('<error>Sorry, for now we have only the command "activities and the subcommands "show", "resume" and "stop".</error>');
        }
    }

    protected function showActivities(OutputInterface $output) {
        $result = $this->client->requestActivities();

        $table = new Table($output);
        $table
            ->setHeaders(array('Id', 'Description', 'Status'))
            ->setRows($result);
        $table->render();
    }

    protected function resumeActivity(OutputInterface $output, $task) {
        if (!$this->checkNumberOfArguments($output, $task)) {
            return;
        }
        $statusCode = $this->client->resumeActivity($task[1]);
        if ($statusCode === 200) {
            $output->writeln('<info>Activity ' . $task[1] . ' resumed</info>');
        } else {
            $output->writeln('<error>Couldn\'t resume activity</error>');
        }
    }

    protected function stopActivity(OutputInterface $output, $task) {
        if (!$this->checkNumberOfArguments($output, $task)) {
            return;
        }
        $statusCode = $this->client->stopActivity($task[1]);
        if ($statusCode === 200) {
            $output->writeln('<info>Activity ' . $task[1] . ' stopped</info>');
        } else {
            $output->writeln('<error>Couldn\'t stop activity</error>');
        }
    }

    protected function checkNumberOfArguments(OutputInterface $output, $task) {
        if (!(isset($task[1]))) {
            $output->writeln('');
            $output->writeln('<error>');
            $output->writeln('                                                                                    ');
            $output->writeln('  Please give a valid activity id: "php dimesh.php activities resume <activity_id>" ');
            $output->writeln('                                                                                    ');
            $output->writeln('</error>');
            return false;
        } else {
            return true;
        }
    }
}
