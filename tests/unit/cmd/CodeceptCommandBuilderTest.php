<?php

namespace tests\unit\cmd;

use branchonline\pgsqltester\cmd\CodeceptCommandBuilder;
use Codeception\Test\Unit;

/**
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class CodeceptCommandBuilderTest extends Unit {

    /** @dataProvider provideExecCommand */
    public function testBuildCommand($command, $config, $expected) {
        $builder = new CodeceptCommandBuilder();
        $builder->executeAction($command);

        foreach ($config as $command => $param) {
            if (empty($param)) {
                $builder->$command();
            } else {
                $builder->$command($param);
            }
        }

        $this->assertSame($expected, $builder->getCommand());
    }

    public function provideExecCommand() {
        return [
            [
                'run',
                [
                    'onSuite'  => 'unit',
                    'onFile'   => 'tests/unit/CodeceptCommandBuilderTest.php',
                    'onFunction' => 'testBuildCommand',
                ],
                'composer exec codecept run -v unit -- tests/unit/CodeceptCommandBuilderTest.php::testBuildCommand'
            ],
            [
                'run',
                [
                    'onSuite'  => 'unit',
                    'onFile'   => 'tests/unit/CodeceptCommandBuilderTest.php',
                ],
                'composer exec codecept run -v unit -- tests/unit/CodeceptCommandBuilderTest.php'
            ],
            [
                'run',
                [
                    'onSuite'  => 'unit',
                    'onModule' => 'cms',
                    'onFile'   => 'tests/unit/CodeceptCommandBuilderTest.php',
                ],
                'composer exec codecept run -v unit -- -c cms tests/unit/CodeceptCommandBuilderTest.php'
            ],
            [
                'run',
                [
                    'onSuite' => 'unit',
                    'onModule' => 'cms',
                    'outputHtmlCoverage' => []
                ],
                'composer exec codecept run -v unit -- -c cms --coverage-html'
            ],
            [
                'run',
                [
                    'onSuite' => 'unit',
                    'onModule' => 'cms',
                ],
                'composer exec codecept run -v unit -- -c cms'
            ],
            [
                'run',
                [
                    'onSuite' => 'unit',
                    'beSilent' => [],
                    'outputHtmlCoverage' => []
                ],
                'composer exec codecept run unit -- --coverage-html',
            ],
            [
                'run',
                ['onSuite' => 'unit'],
                'composer exec codecept run -v unit',
            ],
            [
                'run',
                ['outputHtmlcoverage' => []],
                'composer exec codecept run -v -- --coverage-html',
            ],
            [
                'run',
                [],
                'composer exec codecept run -v',
            ],
            [
                'run',
                ['beSilent' => []],
                'composer exec codecept run',
            ],
            [
                'build',
                ['beSilent' => []],
                'composer exec codecept build',
            ],
        ];
    }

}