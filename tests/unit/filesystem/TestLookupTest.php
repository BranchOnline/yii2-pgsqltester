<?php

namespace branchonline\pgsqltester\filesystem;

use Codeception\Test\Unit;
use InvalidArgumentException;

class TestLookupTest extends Unit {

    public function testInstantiateFromIllegalBasepath() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid base path given.');

        new TestLookup('/unknown/basepath');
    }

    /** @dataProvider canRunProvider */
    public function testBatchIsCorrect($name, $suite, $module, $expected) {
        $lookup = $this->constructLookup();

        $batch = $lookup->lookup($name, $suite, $module);

        $this->assertSame($expected['can_run'], $batch->canRun());
        $this->assertSame($expected['name'], $batch->getNameToRun());
        $this->assertSame($expected['suite'], $batch->getSuiteToRun());
        $this->assertSame($expected['module'], $batch->getModuleToRun());
    }

    public function canRunProvider() {
        return [
            'All in suite unit' => [
                null,
                'unit',
                null,
                [
                    'can_run' => false,
                    'name'    => false,
                    'suite'   => false,
                    'module'  => false,
                ]
            ],
            'All in suite style' => [
                null,
                'style',
                null,
                [
                    'can_run' => true,
                    'name'    => null,
                    'suite'   => 'style',
                    'module'  => 'moduleA',
                ]
            ],
            'All in module A in suite style' => [
                null,
                'unit',
                'moduleA',
                [
                    'can_run' => true,
                    'name'    => null,
                    'suite'   => 'unit',
                    'module'  => 'moduleA',
                ]
            ],
            'All in module A' => [
                null,
                null,
                'moduleA',
                [
                    'can_run' => true,
                    'name'    => null,
                    'suite'   => null,
                    'module'  => 'moduleA',
                ]
            ],
            'All Class A in suite unit in module A' => [
                'classa',
                'unit',
                'moduleA',
                [
                    'can_run' => true,
                    'name'    => static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                    'suite'   => 'unit',
                    'module'  => 'moduleA',
                ]
            ],
            'All Class A in suite unit' => [
                'classa',
                'unit',
                null,
                [
                    'can_run' => false,
                    'name'    => false,
                    'suite'   => false,
                    'module'  => false,
                ]
            ],
            'All Class A' => [
                'classa',
                null,
                null,
                [
                    'can_run' => false,
                    'name'    => false,
                    'suite'   => false,
                    'module'  => false,
                ]
            ],
            'All tests' => [
                null,
                null,
                null,
                [
                    'can_run' => true,
                    'name'    => null,
                    'suite'   => null,
                    'module'  => null,
                ]
            ],
            'All class EEE' => [
                'classeee',
                null,
                null,
                [
                    'can_run' => false,
                    'name'    => false,
                    'suite'   => false,
                    'module'  => false,
                ]
            ],
            'All in integration' => [
                null,
                'integration',
                null,
                [
                    'can_run' => true,
                    'name'    => null,
                    'suite'   => 'integration',
                    'module'  => null,
                ]
            ],
        ];
    }

    /** @dataProvider lookupProvider */
    public function testLookup($name, $suite, $module, $expected) {
        $lookup = $this->constructLookup();

        $batch = $lookup->lookup($name, $suite, $module);
        $this->assertSame(sizeof($expected), $batch->getSize());
        foreach ($batch->getFiles() as $index => $result) {
            $this->assertSame($result->getRelativePath(), $expected[$index]);
        }
    }

    public function lookupProvider() {
        return [
            'All tests' => [
                null,
                null,
                null,
                [
                    static::path('/moduleA/tests/style/ClassETest.php'),
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                    static::path('/moduleA/tests/unit/subsystemA/ClassBTest.php'),
                    static::path('/moduleA/tests/unit/subsystemA/ClassCTest.php'),
                    static::path('/tests/integration/ClassATest.php'),
                    static::path('/tests/unit/subsystemA/ClassATest.php'),
                    static::path('/tests/unit/subsystemA/ClassBTest.php'),
                    static::path('/tests/unit/subsystemB/ClassCTest.php'),
                    static::path('/tests/unit/subsystemB/ClassDTest.php'),
                ]
            ],
            'All Class EEE' => [
                'classeee',
                null,
                null,
                []
            ],
            'All Misspelled Class A' => [
                'clasa',
                null,
                null,
                [
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                    static::path('/tests/integration/ClassATest.php'),
                    static::path('/tests/unit/subsystemA/ClassATest.php'),
                ]
            ],
            'All Class A' => [
                'classa',
                null,
                null,
                [
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                    static::path('/tests/integration/ClassATest.php'),
                    static::path('/tests/unit/subsystemA/ClassATest.php'),
                ]
            ],
            'All Class A in suite unit' => [
                'classa',
                'unit',
                null,
                [
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                    static::path('/tests/unit/subsystemA/ClassATest.php'),
                ]
            ],
            'All Class A in suite unit in module A' => [
                'classa',
                'unit',
                'moduleA',
                [
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                ]
            ],
            'All Class A in module A' => [
                'classa',
                null,
                'moduleA',
                [
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                ]
            ],
            'All in module A in suite style' => [
                null,
                'style',
                'moduleA',
                [
                    static::path('/moduleA/tests/style/ClassETest.php'),
                ]
            ],
            'All in module A' => [
                null,
                null,
                'moduleA',
                [
                    static::path('/moduleA/tests/style/ClassETest.php'),
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                    static::path('/moduleA/tests/unit/subsystemA/ClassBTest.php'),
                    static::path('/moduleA/tests/unit/subsystemA/ClassCTest.php'),
                ]
            ],
            'All in unit' => [
                null,
                'unit',
                null,
                [
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                    static::path('/moduleA/tests/unit/subsystemA/ClassBTest.php'),
                    static::path('/moduleA/tests/unit/subsystemA/ClassCTest.php'),
                    static::path('/tests/unit/subsystemA/ClassATest.php'),
                    static::path('/tests/unit/subsystemA/ClassBTest.php'),
                    static::path('/tests/unit/subsystemB/ClassCTest.php'),
                    static::path('/tests/unit/subsystemB/ClassDTest.php'),
                ]
            ],
            'All in module B' => [
                null,
                null,
                'moduleB',
                [],
            ],
            'All in integration' => [
                null,
                'integration',
                null,
                [
                    static::path('/tests/integration/ClassATest.php'),
                ]
            ]
        ];
    }

    protected static function path(string $path): string {
        if (DIRECTORY_SEPARATOR === '/') {
            return str_replace('\\', DIRECTORY_SEPARATOR, $path);
        } elseif (DIRECTORY_SEPARATOR === '\\') {
            return str_replace('/', DIRECTORY_SEPARATOR, $path);
        } else {
            return $path;
        }
    }

    protected function constructLookup() {
        return new TestLookup(codecept_data_dir() . '_fake_app_base_dir');
    }

}