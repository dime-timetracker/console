# Dime Console
a command line frontend for the Dime timetracker 

Its based on Symfony console (http://symfony.com/doc/current/components/console/introduction.html),
Guzzle (http://docs.guzzlephp.org/en/latest/)
and Stecman Symfony Console Completion (https://github.com/stecman/symfony-console-completion).

For now only the activities command is implemented.
We have the following subcommands:
show, resume, stop, interactive.

If you want the most advanced features of the interactive subcommand you have 
to install the PHP Extension clockthread (https://github.com/ThomasJez/ClockThread)
although this subcommand runs without this too (even though without clocks)

