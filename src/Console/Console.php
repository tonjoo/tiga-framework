<?php namespace Tiga\Framework\Console;

use Tonjoo\Almari\Container;
use Tiga\Framework\Facade\ApplicationFacade as App;
use Symfony\Component\Console\Application;

class Console
{

    // All registered command for the console application
    protected $commands = array();

    // Console Appliation
    protected $consoleApp;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->consoleApp = new Application('Lotus Framework', App::get('version'));
    }

    /**
     * Run tiga console
     */
    public function run()
    {        
        // Add registered command to console 
        foreach ($this->commands as $command) {

            $this->consoleApp->add($command);
        
        }

        $this->consoleApp->run();
    }

    /**
     * Register command to the console application
     * @param \Symfony\Component\Console\Command\Command
     */
    public function registerCommand($command)
    {
        array_push($this->commands,$command);
    }
}