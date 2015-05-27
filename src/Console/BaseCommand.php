<?php 
namespace Tiga\Framework\Console;

// Some sources Taken from Laravel Command

use Tiga\Framework\Facade\ApplicationFacade as App;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tonjoo\Almari\Container;
use \Symfony\Component\Console\Command\Command as Command;

class BaseCommand extends Command
{

	public function __construct()
	{
		parent::__construct();
	}

	protected function showError($title,$messages='',$output)
	{
		$output->writeln("<error>$title</error>");
		$output->writeln("");
		if(is_array($messages))
		{
			foreach ($messages as $message ) 
			{
				$output->writeln("<comment>$message</comment>");
			}
			$output->writeln("");
		}
		elseif($messages!='')
		{
			$output->writeln("<comment>$messages</comment>");
			$output->writeln("");
		}

	}

	protected function showSuccess($title,$messages='',$output)
	{
		$output->writeln("<info>$title</info>");
		$output->writeln("");
		if(is_array($messages))
		{
			foreach ($messages as $message ) 
			{
				$output->writeln("$message");
			}
			$output->writeln("");
		}
		elseif($messages!='')
		{
			$output->writeln($messages);
			$output->writeln("");
		}
		
	}

	protected function runProcess($command,$messages='',$output)
	{
		// Generate autoload file from composer
        $process = new Process($command);

        if(is_array($messages))
		{
			foreach ($messages as $message ) 
			{
				$output->writeln("<info>{$message}</info>");
			}
			$output->writeln("");
		}
		elseif($messages!='')
		{
			$output->writeln("<info>{$messages}</info>");
			$output->writeln("");
		}

        $process->run(function ($type, $buffer) {
		    if (Process::ERR === $type) {
		        echo $buffer;
		    } else {
		        echo $buffer;
		    }
		});

		$output->writeln("");

	}

	/**
	 * Call another console command silently.
	 *
	 * @param  string  $command
	 * @param  array   $arguments
	 * @return integer
	 */
	public function callSilent($command, array $arguments = array())
	{
		$instance = $this->getApplication()->find($command);

		$arguments['command'] = $command;

		return $instance->run(new ArrayInput($arguments), new NullOutput);
	}

	/**
	 * Call another console command.
	 *
	 * @param  string  $command
	 * @param  array   $arguments
	 * @return integer
	 */
	public function call($command, array $arguments = array())
	{
		$instance = $this->getApplication()->find($command);

		$arguments['command'] = $command;

		return $instance->run(new ArrayInput($arguments), $this->output);
	}

}