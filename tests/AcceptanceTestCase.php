<?php

class AcceptanceTestCase extends PHPUnit_Framework_TestCase
{
    protected function runConsole($line = '')
    {
        $executable = __DIR__ . '/../oxid/oxid';

        return shell_exec($executable . ' ' . $line);
    }
}
