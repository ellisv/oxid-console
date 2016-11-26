<?php

class AcceptanceTestCase extends PHPUnit_Framework_TestCase
{

    protected static function runConsole($line = '')
    {
        chdir(__DIR__ . '/../oxid/source');
        return shell_exec(static::getExecutablePath() . ' ' . $line);
    }

    protected static function getExecutablePath()
    {
        return __DIR__ . '/../../bin/oxid';
    }

    protected static function removeDirectory($dir)
    {
        foreach (glob("{$dir}/*") as $file) {
            is_dir($file)
                ? static::removeDirectory($file)
                : unlink($file);
        }

        rmdir($dir);
    }
}
