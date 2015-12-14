<?php

class GenerateMigrationCommandTest extends AcceptanceTestCase
{
    public function testExecute()
    {
        $output = $this->runConsole('g:migration test migration');
        $found = preg_match('/\d{14}_testmigration\.php/', $output, $matches);
        $this->assertSame(1, $found, 'Output must contain a valid generated migration name');

        $migrationsDir = OX_BASE_PATH . 'migration';
        $filename = reset($matches);
        $filepath = $migrationsDir . '/' . $filename;

        $this->assertFileExists($filepath);

        unlink($filepath);
    }
}
