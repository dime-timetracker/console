<?php

namespace DimeConsole;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;

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
                InputArgument::REQUIRED,
                'What do you want to do with your activities?'
            )
            ->addOption(
                'id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Which activity?'
            )
            ->addOption(
                'name',
                null,
                InputOption::VALUE_OPTIONAL,
                'Which activity?'
            )
       ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->client = new DimeClient();
        $task = $input->getArgument('task');
        $activityId = $input->getOption('id');
        if ($activityId === null and $input->getOption('name') !== null) {
            $activities = $this->client->requestActivityNames();
            $activityId = $activities[$input->getOption('name')];
        }
        if ($task === 'show') {
            $this->showActivities($output);
        } elseif ($task === 'resume') {
            if ($activityId === null) {
                throw new \Exception('no activity id given');
            }
            $this->resumeActivity($output, $activityId);
        } elseif ($task === 'stop') {
            if ($activityId === null) {
                throw new \Exception('no activity id given');
            }
            $this->stopActivity($output, $activityId);
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

    protected function resumeActivity(OutputInterface $output, $activityId) {
        $statusCode = $this->client->resumeActivity($activityId);
        if ($statusCode === 200) {
            $output->writeln('<info>Activity ' . $activityId . ' resumed</info>');
        } else {
            $output->writeln('<error>Couldn\'t resume activity</error>');
        }
    }

    protected function stopActivity(OutputInterface $output, $activityId) {
        $statusCode = $this->client->stopActivity($activityId);
        if ($statusCode === 200) {
            $output->writeln('<info>Activity ' . $activityId . ' stopped</info>');
        } else {
            $output->writeln('<error>Couldn\'t stop activity</error>');
        }
    }
}
