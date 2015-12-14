<?php

class GenerateModuleCommandTest extends AcceptanceTestCase
{
    public function testGenerate()
    {
        $moduleDir = OX_BASE_PATH . 'modules';
        $testModuleDir = $moduleDir . '/eli/test';
        if (file_exists($testModuleDir)) {
            $this->removeDirectory($testModuleDir);
        }

        $descriptorspec = array(
            array('pipe', 'r'), // stdout
            array('pipe', 'w')  // stdin
        );

        $process = proc_open($this->getExecutablePath() . ' g:module', $descriptorspec, $pipes);
        if (!is_resource($process)) {
            throw new Exception('Could not open console command');
        }

        $this->fillPrompt($pipes[0], 'eli');
        $this->fillPrompt($pipes[0], 'Test');
        $this->fillPrompt($pipes[0], 'John Doe');
        $this->fillPrompt($pipes[0], 'https://johndoe.io/');
        $this->fillPrompt($pipes[0], 'johndoe@gmail.com');
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        proc_close($process);

        $this->assertContains('success', $output);

        // Assert file directory has been created
        $this->assertFileExists($testModuleDir);

        // Assert if vendor metadata file was created in vendor directory
        $this->assertFileExists(dirname($testModuleDir) . '/vendormetadata.php');

        // Assert if metadata file has been created
        $this->assertFileExists($testModuleDir . '/metadata.php');

        // Assert if events file has been created
        $this->assertFileExists($testModuleDir . '/core/elitestevents.php');

        // Assert actual metadata file
        $metadata = $this->fetchMetadata($testModuleDir . '/metadata.php');
        $this->assertMetadata($metadata);
    }

    private function fetchMetadata($path)
    {
        global $aModule;

        include $path;

        return $aModule;
    }

    private function assertMetadata($metadata)
    {
        $this->assertEquals('elitest', $metadata['id']);
        $this->assertEquals('John Doe', $metadata['author']);
        $this->assertEquals('https://johndoe.io/', $metadata['url']);
        $this->assertEquals('johndoe@gmail.com', $metadata['email']);

        $this->assertEquals('eli/test/core/elitestevents.php', $metadata['files']['elitestevents']);

        $this->assertEquals('eliTestEvents::onActivate', $metadata['events']['onActivate']);
        $this->assertEquals('eliTestEvents::onDeactivate', $metadata['events']['onDeactivate']);
    }

    private function fillPrompt(&$pipe, $answer)
    {
        sleep(0.5);
        fwrite($pipe, $answer . PHP_EOL);
    }

    private function removeDirectory($dir)
    {
        foreach (glob("{$dir}/*") as $file) {
            is_dir($file)
                ? $this->removeDirectory($file)
                : unlink($file);
        }

        rmdir($dir);
    }
}
