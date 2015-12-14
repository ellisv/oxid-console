<?php

class MigrateCommandTest extends AcceptanceTestCase
{
    const FILENAME = '20151214134401_dummytest.php';

    public function testRunsMigrationOnce()
    {
        $output = $this->runConsole('migrate');
        $this->assertGoesUp($output);

        $output = $this->runConsole('migrate');
        $this->assertNotGoesUp($output);
    }

    public function testGoesDownWithPastDateOnce()
    {
        $this->runConsole('migrate');

        $output = $this->runConsole('migrate 2014-01-01');
        $this->assertGoesDown($output);

        $output = $this->runConsole('migrate 2014-01-01');
        $this->assertNotGoesDown($output);
    }

    public function testGoesUpAgainWhenWentDown()
    {
        $this->runConsole('migrate');

        $output = $this->runConsole('migrate 2014-01-01');
        $this->assertGoesDown($output);

        $output = $this->runConsole('migrate');
        $this->assertGoesUp($output);
    }

    private function assertGoesUp($output)
    {
        $this->assertContains('Dummy migration goes up', $output);
    }

    private function assertNotGoesUp($output)
    {
        $this->assertNotContains('Dummy migration goes up', $output);
    }

    private function assertGoesDown($output)
    {
        $this->assertContains('Dummy migration goes down', $output);
    }

    private function assertNotGoesDown($output)
    {
        $this->assertNotContains('Dummy migration goes down', $output);
    }

    public function tearDown()
    {
        oxDb::getDb()->execute('DELETE FROM oxmigrationstatus WHERE version = ?', array(static::FILENAME));
    }

    public static function setUpBeforeClass()
    {
        $content = <<<PHP
<?php

class DummyTestMigration extends oxMigrationQuery
{
    public function up()
    {
        echo 'Dummy migration goes up';
    }

    public function down()
    {
        echo 'Dummy migration goes down';
    }
}
PHP;

        file_put_contents(static::getMigrationPath() . '/' . static::FILENAME, $content);
    }

    public static function tearDownAfterClass()
    {
        unlink(static::getMigrationPath() . '/' . static::FILENAME);
    }

    private static function getMigrationPath()
    {
        return OX_BASE_PATH . 'migration';
    }
}
