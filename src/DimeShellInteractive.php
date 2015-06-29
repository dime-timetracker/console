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

    /**
     * @param DimeShellController $controller
     */
    public function __construct(DimeShellController $controller) {
        $this->controller = $controller;
    }

    /**
     * @param OutputInterface $output
     * @param InputInterface $input
     */
    public function run(OutputInterface $output, InputInterface $input) {
        do {
            $this->activities = $this->controller->requestActivities();
            $this->show($output);
        } while ($this->action() === true);
        printf("\x1B[%d;1H", sizeof($this->activities) + 5); //move the cursor in the right place before finishing the app
    }

    /**
     * @param OutputInterface $output
     */
    protected function show(OutputInterface $output) {
        echo "\x1B[2J";  //clears the screen
        echo "\x1B[1;1H";  // move the cursor to position 1,1
        $table = new Table($output);
        $table
            ->setHeaders(array('Id', 'Description', 'Status'))
            ->setRows($this->activities);
        $table->render();
        echo "\x1B[4;3H"; //move the cursor to position 4,3
    }

    /**
     * @return bool
     */
    protected function action() {
        readline_callback_handler_install('', function () {
        });
        $line = 0;
        $continueAction = true;
        while (true) {
            $r = array(STDIN);
            $w = null;
            $e = null;
            $n = stream_select($r, $w, $e, 0); //read keystroke(s)
            if ($n && in_array(STDIN, $r)) {
                $pressedKey = stream_get_contents(STDIN, 1);
                if (ord($pressedKey) === 65 and $line > 0) { //is arrow up pressed?
                    echo "\x1B[1A";       //move the cursor one rom up
                    $line--;
                }
                if (ord($pressedKey) === 66 and $line < sizeof($this->activities) - 1) {   //is arrow down pressed?
                    echo "\x1B[1B";   //move the cursor one row down
                    $line++;
                }
                if ($pressedKey === 'q') {
                    $continueAction = false;
                    break;
                }
                if (ord($pressedKey) === 10) {  //is ENTER pressed?
                    if ($this->activities[$line]['status'] === 'inactive') {
                        $this->controller->resumeActivity($this->activities[$line]['id']);
                    } else {
                        $this->controller->stopActivity($this->activities[$line]['id']);
                    }
                    break;
                }
            }
        }
        readline_callback_handler_remove();  //reenable standard cli features
        return $continueAction;
    }
}
