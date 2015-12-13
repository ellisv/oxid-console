<?php

class CacheClearCommandTest extends AcceptanceTestCase
{
    protected static $cacheDir;

    public static function setUpBeforeClass()
    {
        static::$cacheDir = oxRegistry::getConfig()->getConfigParam('sCompileDir');
    }

    public function testClearingNewlyCreatedFile()
    {
        $path = static::$cacheDir . '/randfile';

        touch($path);
        $this->assertFileExists($path);

        $this->runConsole('cache:clear');
        $this->assertFileNotExists($path);
    }

    public function testNotDeletingExcpetionalPathes()
    {
        if (!file_exists(static::$cacheDir . '/.htaccess')) {
            touch(static::$cacheDir . '/.htaccess');
        }

        if (!file_exists(static::$cacheDir . '/smarty')) {
            mkdir(static::$cacheDir . '/smarty');
        }

        $this->runConsole('cache:clear');
        $this->assertFileExists(static::$cacheDir . '/.htaccess');
        $this->assertFileExists(static::$cacheDir . '/smarty');
    }

    public function testClearsOutSmartyOnly()
    {
        $smartyCacheFile = static::$cacheDir . '/smarty/dummysmartycache';
        $regularCacheFile = static::$cacheDir . '/sumcrazydummycachenotsmarty';

        touch($smartyCacheFile);
        touch($regularCacheFile);

        $this->runConsole('cache:clear --smarty');
        $this->assertFileNotExists($smartyCacheFile);
        $this->assertFileExists($regularCacheFile);
    }

    public function testRemovesDirectories()
    {
        $dir = static::$cacheDir . '/zawesomedir';
        mkdir($dir);
        touch($dir . '/anotherfile');

        $this->runConsole('cache:clear');
        $this->assertFileNotExists($dir);
    }
}
