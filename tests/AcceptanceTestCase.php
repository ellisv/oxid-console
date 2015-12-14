<?php

class AcceptanceTestCase extends PHPUnit_Framework_TestCase
{
    protected function runConsole($line = '')
    {
        return shell_exec($this->getExecutablePath() . ' ' . $line);
    }

    protected function getExecutablePath()
    {
        return __DIR__ . '/../oxid/oxid';
    }
}
