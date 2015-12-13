<?php

class ListCommandTest extends AcceptanceTestCase
{
    public function testDefaultCommandIsList()
    {
        $defaultOutput = $this->runConsole();
        $listOutput = $this->runConsole('list');

        $this->assertEquals($defaultOutput, $listOutput, 'Default command should provide same output as list command');
    }

    public function testListHasAllCommandsRegistered()
    {
        $commands = array(
            'cache:clear',
            'db:update',
            'fix:states',
            'g:migration',
            'g:module',
            'list',
            'migrate',
        );

        $output = $this->runConsole('list');

        foreach ($commands as $command) {
            $this->assertContains($command, $output, "There should be $command available in a list");
        }
    }
}
