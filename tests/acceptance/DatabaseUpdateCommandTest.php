<?php

class DatabaseUpdateCommandTest extends AcceptanceTestCase
{
    public function testExecute()
    {
        $output = $this->runConsole('db:update');
        $this->assertContains('success', $output);
    }
}
