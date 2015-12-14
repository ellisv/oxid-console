<?php

class FixStatesCommandTest extends AcceptanceTestCase
{
    public function testFixesTestModuleWithAllFlag()
    {
        $output = $this->runConsole('fix:states -a');
        $this->assertFixing($output, 'elifixstatestest');
    }

    public function testFixesSpecifiedModule()
    {
        $output = $this->runConsole('fix:states elifixstatestest');
        $this->assertFixing($output, 'elifixstatestest');
    }

    private function assertFixing($output, $moduleId)
    {
        $this->assertContains('Fixing ' . $moduleId, $output);
    }

    public static function setUpBeforeClass()
    {
        $dir = static::getTestModuleDir();
        mkdir($dir, 0777, true);
        touch(dirname($dir) . '/vendormetadata.php');

        $metadata = <<<'PHP'
<?php

$sMetadataVersion = '1.1';

$aModule = array(
    'id'          => 'elifixstatestest',
    'title'       => 'Testing module',
    'description' => 'A module generated for testing',
    'thumbnail'   => '',
    'version'     => '0.0.0',
    'author'      => 'john@doe.com',
    'extend'      => array(
    ),
    'files'       => array(
    ),
    'blocks'      => array(
    ),
);
PHP;

        file_put_contents($dir . '/metadata.php', $metadata);
    }

    public static function tearDownAfterClass()
    {
        static::removeDirectory(static::getTestModuleDir());
    }

    private static function getTestModuleDir()
    {
        return OX_BASE_PATH . 'modules/eli/fixstatestest';
    }
}
