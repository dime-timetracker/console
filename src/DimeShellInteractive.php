<?php

namespace DimeConsole;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * Class DimeShellInteractive
 * @package DimeConsole
 * @author Thomas Jez
 *
 * a small curses like interactive dime console
 *
 * (the escape sequences and key codes look messy but HoaConsole or PHP Ncurses would cost more then its worth in
 * this case
 *
 * @todo: perhaps encapsulate the commands with escape sequences and keyboard codes
 */
class DimeShellInteractive
{
    protected $controller;
    protected $activities;
    protected $hasClockThread;

    /**
     * @param DimeShellController $controller
     */
    public function __construct(DimeShellController $controller) {
        $this->controller = $controller;
        $this->hasClockThread = extension_loaded('clockthread');
    }

    /**
     * @param OutputInterface $output
     * @param InputInterface $input
     */
    public function run(OutputInterface $output, InputInterface $input) {
        $clockId = 0;
        do {
            $clock = array();
            $this->activities = $this->controller->requestActivities();
            foreach ($this->activities as $line => $activity) {
                if ($activity['started'] !== 'inactive') {
                    $clock[$line] = (new \DateTime($activity['started']))->getTimestamp();
                }
            }
            $this->show($output);
            if ($this->hasClockThread) {
                $clockId = clock_start(sizeof($this->activities), $clock);
                list($action, $line) = $this->action(sizeof($this->activities));
                clock_stop($clockId);
            } else {
                list($action, $line) = $this->action(sizeof($this->activities));
            }
            $actionItem = $this->activities[$line]['id'];
            if ($action === 'enter') {
                if ($this->activities[$line]['started'] === 'inactive') {
                    $this->controller->resumeActivity($actionItem);
                } else {
                    $this->controller->stopActivity($actionItem);
                }
            }
        } while ($action !== 'quit');
        $y = 5;
        if (!$this->hasClockThread) {
            $y = 8;
        }
        printf("\x1B[%d;1H", sizeof($this->activities) + $y); //move the cursor in the right place before finishing the app
    }

    /**
     * @param OutputInterface $output
     */
    protected function show(OutputInterface $output) {
        echo "\x1B[2J";  //clears the screen
        echo "\x1B[1;1H";  // move the cursor to position 1,1
        $table = new Table($output);
        if ($this->hasClockThread) {
            $table->setHeaders(array('Id', 'Description', 'Duration'));
        } else {
            $table->setHeaders(array('Id', 'Description', 'Start'));
        }
        $table->setRows($this->activities);
        $table->render();
        $y = 4;
        $x = 3;
        if (!$this->hasClockThread) {
            $output->writeln('<comment>');
            $output->writeln('For full functionality please install the clockthread extension');
            $output->writeln('(https://github.com/ThomasJez/ClockThread)');
            $output->writeln('</comment>');
        }
        printf("\x1B[%d;%dH", $y, $x); //move the cursor to position 4,3
    }

    /**
     * @return bool
     */
    protected function action($anzActivities) {
        readline_callback_handler_install('', function (){});
        $line = 0;
        $action = 'undefined';
        while (true) {
            $r = array(STDIN);
            $w = null;
            $e = null;
            $n = stream_select($r, $w, $e, 0); //read keystroke(s)
            if ($n && in_array(STDIN, $r)) {
                $pressedKey = stream_get_contents(STDIN, 1);
                if (ord($pressedKey) === 65 and $line > 0) { //is arrow up pressed?
//                    echo "\x1B[1A";       //move the cursor one rom up
                    $line--;
                    clock_return2line($line);
                    printf("\x1B[%d;%dH", $line + 4, 3);
                }
                if (ord($pressedKey) === 66 and $line < $anzActivities - 1) {   //is arrow down pressed?
//                    echo "\x1B[1B";   //move the cursor one row down
                    $line++;
                    clock_return2line($line);
                    printf("\x1B[%d;%dH", $line + 4, 3);
                }
                if ($pressedKey === 'q') {
                    $action = 'quit';
                    break;
                }
                if (ord($pressedKey) === 10) {  //is ENTER pressed?
                    $action = 'enter';
                    break;
                }
            }
        }
        readline_callback_handler_remove();  //reenable standard cli features
        return array($action, $line);
    }
}
