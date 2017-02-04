<?php

namespace Fumizp\php7ccc\Infrastructure;

use Sstalle\php7cc\Infrastructure\ContainerBuilder;
use Sstalle\php7cc\Infrastructure\PHP7CCCommand;
use Sstalle\php7cc\PathCheckSettings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PHP7CCCCommand extends PHP7CCCommand
{
    const COMMAND_NAME = 'php7ccc';

    protected function configure()
    {
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = $input->getArgument(static::PATHS_ARGUMENT_NAME);
        foreach ($paths as $path) {
            if (!is_file($path) && !is_dir($path)) {
                $output->writeln(sprintf('Path %s must be a file or a directory', $path));

                return;
            }
        }

        $extensionsArgumentValue = $input->getOption(static::EXTENSIONS_OPTION_NAME);
        $extensions = explode(',', $extensionsArgumentValue);
        if (!is_array($extensions)) {
            $output->writeln(
                sprintf(
                    'Something went wrong while parsing file extensions you specified. ' .
                    'Check that %s is a comma-separated list of extensions',
                    $extensionsArgumentValue
                )
            );

            return;
        }

        $messageLevelName = $input->getOption(static::MESSAGE_LEVEL_OPTION_NAME);
        if (!isset(static::$messageLevelMap[$messageLevelName])) {
            $output->writeln(sprintf('Unknown message level %s', $messageLevelName));

            return;
        }
        $messageLevel = static::$messageLevelMap[$messageLevelName];

        $intSize = (int) $input->getOption(static::INT_SIZE_OPTION_NAME);
        if ($intSize <= 0) {
            $output->writeln('Integer size must be greater than 0');

            return;
        }

        $containerBuilder = new ContainerBuilder();
        $container = $containerBuilder->buildContainer($output, $intSize);
        $container['resultPrinter'] = function ($c) {
            return new \Fumizp\php7ccc\SummarizedResultPrinter($c['output'], $c['nodePrinter'], $c['nodeStatementsRemover']);
        };

        $checkSettings = new PathCheckSettings($paths, $extensions);
        $checkSettings->setExcludedPaths($input->getOption(static::EXCEPT_OPTION_NAME));
        $checkSettings->setMessageLevel($messageLevel);
        $checkSettings->setUseRelativePaths($input->getOption(static::RELATIVE_PATHS_OPTION_NAME));

        $container['pathCheckExecutor']->check($checkSettings);

        $container['resultPrinter']->summarize();
    }
}
