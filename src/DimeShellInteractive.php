<?php

namespace DimeConsole;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class DimeShellInteractive
{
    protected $client;

    public function __construct(DimeClient $client) {
        $this->client = $client;
    }

    public function run(OutputInterface $output, InputInterface $input) {
        do {
            $result = $this->client->requestActivities();
            echo "\x1B[2J";  //clears the screen
            echo "\x1B[1;1H";  // move the cursor to position 1,1
            $table = new Table($output);
            $table
                ->setHeaders(array('Id', 'Description', 'Status'))
                ->setRows($result);
            $table->render();
            echo "\x1B[4;3H"; //move the cursor to position 4,3

            //disables standard cli features (echo, prompt, etc.)
            readline_callback_handler_install('', function () {
            });
            $line = 0;
            $quit = false;
            while (true) {
                $r = array(STDIN);
                $w = null;
                $e = null;
                $n = stream_select($r, $w, $e, 0); //read keystroke(s)
                if ($n && in_array(STDIN, $r)) {
                    $c = stream_get_contents(STDIN, 1);
                    if (ord($c) === 65 and $line > 0) { //is arrow up pressed?
                        echo "\x1B[1A";       //move the cursor one rom up
                        $line--;
                    }
                    if (ord($c) === 66 and $line < sizeof($result) - 1) {   //is arrow down pressed?
                        echo "\x1B[1B";   //move the cursor one row down
                        $line++;
                    }
                    if ($c === 'q') {
                        $quit = true;
                        break;
                    }
                    if (ord($c) === 10) {  //is ENTER pressed?
                        if ($result[$line]['status'] === 'inactive') {
                            $this->client->resumeActivity($result[$line]['id']);
                        } else {
                            $this->client->stopActivity($result[$line]['id']);
                        }
                        break;
                    }
                }
            }
            readline_callback_handler_remove();  //reenable standard cli features
        } while ($quit === false);
        printf("\x1B[%d;1H", sizeof($result) + 5);
    }
}
