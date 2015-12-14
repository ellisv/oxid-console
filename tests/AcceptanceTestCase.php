<?php

class AcceptanceTestCase extends PHPUnit_Framework_TestCase
{
    protected static function runConsole($line = '')
    {
        return shell_exec(static::getExecutablePath() . ' ' . $line);
    }

    protected static function getExecutablePath()
    {
        return __DIR__ . '/../oxid/oxid';
    }
}
