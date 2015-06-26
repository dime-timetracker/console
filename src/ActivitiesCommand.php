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
        } elseif ($task === 'interactive') {
            $this->activitiesInteractive($output, $input);
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

    protected function activitiesInteractive(OutputInterface $output, InputInterface $input) {
        do {
            $result = $this->client->requestActivities();
            echo "\x1B[2J";
            echo "\x1B[1;1H";
            $table = new Table($output);
            $table
                ->setHeaders(array('Id', 'Description', 'Status'))
                ->setRows($result);
            $table->render();
            echo "\x1B[4;3H";
            readline_callback_handler_install('', function () {
            });
            $line = 0;
            $quit = false;
            while (true) {
                $r = array(STDIN);
                $w = null;
                $e = null;
                $n = stream_select($r, $w, $e, 0);
                if ($n && in_array(STDIN, $r)) {
                    $c = stream_get_contents(STDIN, 1);
                    if (ord($c) === 65 and $line > 0) {
                        echo "\x1B[1A";
                        $line--;
                    }
                    if (ord($c) === 66 and $line < sizeof($result) - 1) {
                        echo "\x1B[1B";
                        $line++;
                    }
                    if ($c === 'q') {
                        $quit = true;
                        break;
                    }
                    if (ord($c) === 10) {
                        if ($result[$line]['status'] === 'inactive') {
                            $this->client->resumeActivity($result[$line]['id']);
                        } else {
                            $this->client->stopActivity($result[$line]['id']);
                        }
                        break;
                    }
                }
            }
            readline_callback_handler_remove();
        } while ($quit === false);
        printf("\x1B[%d;1H", sizeof($result) + 5);
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
