<?php

namespace tests\unit\cmd;

use branchonline\pgsqltester\cmd\BuildCommandConstructor;
use Codeception\Test\Unit;

/**
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class BuildCommandConstructorTest extends Unit {

    public function testStandardCommand() {
        $constructor = new BuildCommandConstructor(false);
        $this->assertEquals('composer exec codecept build -v', $constructor->getCommand());
    }

    public function testSilentCommand() {
        $constructor = new BuildCommandConstructor(true);
        $this->assertEquals('composer exec codecept build', $constructor->getCommand());
    }

}