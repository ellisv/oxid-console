<?php

/*
 * This file is part of the OXID Console package.
 *
 * (c) Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EllisV\Oxid\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;

/**
 * The console application that handles the commands
 *
 * @author Eligijus Vitkauskas <eligijusvitkauskas@gmail.com>
 */
class Application extends BaseApplication
{
    const VERSION = '2.0.0-DEV';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('OXID Console', self::VERSION);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption(
            '--working-dir', '-d',
            InputOption::VALUE_REQUIRED,
            'If specified, use the given directory as working directory'
        ));

        return $definition;
    }
}
