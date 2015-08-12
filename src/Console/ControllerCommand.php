<?php


namespace Tiga\Framework\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tiga\Framework\Template\Template;
use Config;

/**
 * Generate Controller Command.
 */
class ControllerCommand extends BaseCommand
{
    /**
     * Configure ControllerCommand.
     */
    protected function configure()
    {
        $this->setName('generate:controller');

        $this->setDescription('Generate Controller');

        $this->addArgument(
                'class',
                InputArgument::REQUIRED,
                'Controller class name'
            );

        $this->addArgument(
                'method',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Controller method'
            );
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check if Controller File Exist
        $controllerName = ucfirst($input->getArgument('class'));

        $controllerName = $controllerName;

        $output->writeln('');
        $output->writeln("<info>Generating $controllerName...</info>");
        $output->writeln('');

        $controllerPath = Config::get(TIGA_INSTANCE.'.controller');

        // Check if file already exist
        if (file_exists($controllerPath.$controllerName.'.php')) {
            $this->showError('Cannot create Controller', "File /app/controllers/{$controllerName}.php exist", $output);

            return;
        }

        // Check if path writable
        if (!is_writable($controllerPath)) {
            $this->showError('Path not writable', "Cannot write to {$controllerPath}", $output);

            return;
        }

        $config['path'] = __DIR__.'/Templates/';

        $templateEngine = new Template($config);

        $data['controllerName'] = $controllerName;

        $data['methods'] = $input->getArgument('method');

        $controllerContent = $templateEngine->render('controller.template', $data);

        file_put_contents($controllerPath.$controllerName.'.php', $controllerContent);

        // Composer dump Autoload
        $this->runProcess('composer dump-autoload', 'Running : composer dump-autoload', $output);

        $message[0] = 'File location : /app/Controllers/'.$controllerName.'.php';

        // Finish  
        $this->showSuccess("{$controllerName} generated", $message, $output);
    }
}
