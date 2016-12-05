<?php

class GenerateModuleCommandTest extends AcceptanceTestCase
{
    public function testDirectoryCreated()
    {
        $testModuleDir = $this->getTestModuleDir();
        $this->assertFileExists($testModuleDir);
    }

    public function testVendorMetadataCreated()
    {
        $testModuleDir = $this->getTestModuleDir();
        $this->assertFileExists(dirname($testModuleDir) . '/vendormetadata.php');
    }

    public function testMetadata()
    {
        $testModuleDir = $this->getTestModuleDir();
        $this->assertFileExists($testModuleDir . '/metadata.php');

        global $aModule;
        include $testModuleDir . '/metadata.php';

        $metadata = $aModule;

        $this->assertEquals('elitest', $metadata['id']);
        $this->assertEquals('John Doe', $metadata['author']);
        $this->assertEquals('https://johndoe.io/', $metadata['url']);
        $this->assertEquals('johndoe@gmail.com', $metadata['email']);

        $this->assertEquals('eli/test/core/elitestevents.php', $metadata['files']['elitestevents']);

        $this->assertEquals('eliTestEvents::onActivate', $metadata['events']['onActivate']);
        $this->assertEquals('eliTestEvents::onDeactivate', $metadata['events']['onDeactivate']);
    }

    public function testEventsClassCreated()
    {
        $testModuleDir = $this->getTestModuleDir();
        $this->assertFileExists($testModuleDir . '/core/elitestevents.php');
    }

    public static function setUpBeforeClass()
    {
        $testModuleDir = static::getTestModuleDir();
        if (file_exists($testModuleDir)) {
            static::removeDirectory($testModuleDir);
        }

        $descriptorspec = array(
            array('pipe', 'r'),
            array('pipe', 'w')
        );

        // We enforce command to run in interactive mode.
        // See: https://github.com/symfony/symfony/blob/v2.8.0/src/Symfony/Component/Console/Application.php#L800
        putenv('SHELL_INTERACTIVE=true');

        $process = proc_open(static::getExecutablePath() . ' g:module', $descriptorspec, $pipes, OX_BASE_PATH);
        if (!is_resource($process)) {
            throw new Exception('Could not open console command');
        }

        static::fillPrompt($pipes[0], 'eli');
        static::fillPrompt($pipes[0], 'Test');
        static::fillPrompt($pipes[0], 'John Doe');
        static::fillPrompt($pipes[0], 'https://johndoe.io/');
        static::fillPrompt($pipes[0], 'johndoe@gmail.com');
        fclose($pipes[0]);
        stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        proc_close($process);
    }

    public static function tearDownAfterClass()
    {
        $testModuleDir = static::getTestModuleDir();
        if (file_exists($testModuleDir)) {
            static::removeDirectory($testModuleDir);
        }
    }

    private static function getTestModuleDir()
    {
        return OX_BASE_PATH . 'modules/eli/test';
    }

    private static function fillPrompt(&$pipe, $answer)
    {
        sleep(0.5);
        fwrite($pipe, $answer . PHP_EOL);
    }
}
