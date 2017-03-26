<?php

namespace tests\unit\cmd;
use branchonline\pgsqltester\cmd\RunCommandConstructor;
use Codeception\Test\Unit;

/**
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class RunCommandConstructorTest extends Unit {

    /** @dataProvider runCommandsProvider */
    public function testCommands($command_constructor, $expected) {
        $this->assertEquals($expected, $command_constructor->getCommand());
    }

    public function testSetFunction() {
        $constructor = new RunCommandConstructor(null, null, 'components/Test.php');
        $constructor->setFunction('testFunctie');
        $this->assertEquals('composer exec codecept run -v -- components/Test.php::testFunctie', $constructor->getCommand());
    }

    public function runCommandsProvider() {
        return [
            [
                new RunCommandConstructor(),
                'composer exec codecept run -v',
            ],
            [
                new RunCommandConstructor(null, 'unit'),
                'composer exec codecept run -v unit',
            ],
            [
                new RunCommandConstructor('', 'unit'),
                'composer exec codecept run -v unit',
            ],
            [
                new RunCommandConstructor('cms', 'unit', 'components/Test.php', 'testFunctie', true, true),
                'composer exec codecept run unit -- -c cms components/Test.php::testFunctie --coverage-html',
            ]
        ];
    }

}