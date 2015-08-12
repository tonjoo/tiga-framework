<?php


namespace Tiga\Framework\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tiga\Framework\Template\Template;
use Config;

/**
 * Generate Model Command.
 */
class ModelCommand extends BaseCommand
{
    /**
     * Configure ControllerCommand.
     */
    protected function configure()
    {
        $this->setName('generate:model');

        $this->setDescription('Generate Model');

        $this->addArgument(
                'class',
                InputArgument::REQUIRED,
                'Model class name'
            );

        $this->addArgument(
                'method',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Model method'
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
        // Check if Model File Exist
        $modelName = ucfirst($input->getArgument('class'));

        $modelName = $modelName;

        $output->writeln('');
        $output->writeln("<info>Generating $modelName...</info>");
        $output->writeln('');

        $modelPath = Config::get(TIGA_INSTANCE.'.model');

        // Check if file already exist
        if (file_exists($modelPath.$modelName.'.php')) {
            $this->showError('Cannot create Model', "File /app/models/{$modelName}.php exist", $output);

            return;
        }

        // Check if path writable
        if (!is_writable($modelPath)) {
            $this->showError('Path not writable', "Cannot write to {$modelPath}", $output);

            return;
        }

        $config['path'] = __DIR__.'/Templates/';

        $templateEngine = new Template($config);

        $data['modelName'] = $modelName;

        $data['methods'] = $input->getArgument('method');

        $modelContent = $templateEngine->render('model.template', $data);

        file_put_contents($modelPath.$modelName.'.php', $modelContent);

        // Composer dump Autoload
        $this->runProcess('composer dump-autoload', 'Running : composer dump-autoload', $output);

        $message[0] = 'File location : /app/Models/'.$modelName.'.php';

        // Finish  
        $this->showSuccess("{$modelName} generated", $message, $output);
    }
}
