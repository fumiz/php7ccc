<?php

namespace Fumizp\php7ccc\Infrastructure;

use Symfony\Component\Console\Input\InputInterface;

class Application extends \Symfony\Component\Console\Application
{
    const VERSION = '1.1.0';

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('PHP 7 Compatibility Checker', static::VERSION);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();

        return $inputDefinition;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        return PHP7CCCCommand::COMMAND_NAME;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new PHP7CCCCommand();

        return $defaultCommands;
    }
}
