<?php

namespace tests\unit\cmd;

use branchonline\pgsqltester\cmd\BuildCommand;
use Codeception\Test\Unit;

/**
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class BuildCommandTest extends Unit {

    public function testStandardCommand() {
        $command = new BuildCommand(false);
        $this->assertEquals('composer exec codecept build -v', $command->getCommandString());
    }

    public function testSilentCommand() {
        $command = new BuildCommand(true);
        $this->assertEquals('composer exec codecept build', $command->getCommandString());
    }

}