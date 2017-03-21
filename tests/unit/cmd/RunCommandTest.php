<?php

namespace tests\unit\cmd;
use branchonline\pgsqltester\cmd\RunCommand;
use Codeception\Test\Unit;

/**
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class RunCommandTest extends Unit {

    /** @dataProvider runCommandsProvider */
    public function testCommands($command_instance, $expected) {
        $this->assertEquals($expected, $command_instance->getCommandString());
    }

    public function runCommandsProvider() {
        return [
            [
                new RunCommand(),
                'composer exec codecept run -v',
            ],
            [
                new RunCommand(null, 'unit'),
                'composer exec codecept run -v unit',
            ],
            [
                new RunCommand('', 'unit'),
                'composer exec codecept run -v unit',
            ],
            [
                new RunCommand('cms', 'unit', 'components/Test.php', 'testFunctie', true, true),
                'composer exec codecept run unit --coverage-html -- -c cms components/Test.php::testFunctie',
            ]
        ];
    }

}