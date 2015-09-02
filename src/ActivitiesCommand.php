<?php

namespace DimeConsole;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * Class ActivitiesCommand
 * @package DimeConsole
 *
 * @author Thomas Jez
 * implements the activities command for the Symfony console
 */
class ActivitiesCommand extends Command
{
    protected $controller;

    /**
     *
     */
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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->controller = new DimeShellController($this->getApplication()->getServices()->get('client'));
        $task = $input->getArgument('task');
        $activityId = $input->getOption('id');
        if ($activityId === null and $input->getOption('name') !== null) {
            $activities = $this->controller->requestActivityNames();
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
        } elseif ($task === 'interactive') {
            $this->activitiesInteractive($output, $input);
        } else {
            $output->writeln('<error>Sorry, for now we have only the command "activities and the subcommands "show", "resume", "stop" and "interactive".</error>');
        }
    }

    /**
     * @param OutputInterface $output
     */
    protected function showActivities(OutputInterface $output) {
        $result = $this->controller->requestActivities();

        $table = new Table($output);
        $table
            ->setHeaders(array('Id', 'Description', 'Time'))
            ->setRows($result);
        $table->render();
    }

    /**
     * @param OutputInterface $output
     * @param InputInterface $input
     */
    protected function activitiesInteractive(OutputInterface $output, InputInterface $input) {
        $interactive = new DimeShellInteractive($this->controller);
        $interactive->run($output, $input);
    }

    /**
     * @param OutputInterface $output
     * @param $activityId
     */
    protected function resumeActivity(OutputInterface $output, $activityId) {
        $statusCode = $this->controller->resumeActivity($activityId);
        if ($statusCode === 200) {
            $output->writeln('<info>Activity ' . $activityId . ' resumed</info>');
        } else {
            $output->writeln('<error>Couldn\'t resume activity</error>');
        }
    }

    /**
     * @param OutputInterface $output
     * @param $activityId
     */
    protected function stopActivity(OutputInterface $output, $activityId) {
        $statusCode = $this->controller->stopActivity($activityId);
        if ($statusCode === 200) {
            $output->writeln('<info>Activity ' . $activityId . ' stopped</info>');
        } else {
            $output->writeln('<error>Couldn\'t stop activity</error>');
        }
    }
}
