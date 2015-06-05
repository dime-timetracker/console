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
        } else {
            $output->writeln('<error>Sorry, for now we have only the command "activities and the subcommands "show" and "resume"</error>');
        }
    }

    protected function showActivities(OutputInterface $output) {
        $result = $this->client->requestActivities();

        $table = new Table($output);
        $table
            ->setHeaders(array('Id', 'Description'))
            ->setRows($result);
        $table->render();
    }

    protected function resumeActivity(OutputInterface $output, $task) {
        $result = $this->client->requestActivities();
        $activityIds = [];
        foreach ($result as $line) {
            $activityIds[] = $line['id'];
        }
        if (!(isset($task[1]) and in_array($task[1], $activityIds))) {
            $output->write('<error>Please give a valid activity Id "php dimesh.php activities resume <activity_id>"');
            $output->writeln('</error>');
            $output->writeln('<comment>You have the following activities:');
            $this->showActivities($output);
            $output->writeln('</comment>');
            return;
        }
        $statusCode = $this->client->resumeActivity($task[1]);
        if ($statusCode === 200) {
            $output->writeln('<info>Activitiy resumed</info>');
        } else {
            $output->writeln('<error>Couldn\'t resume activity</error>');
        }
    }
}
