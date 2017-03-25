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

    /** @dataProvider lookupProvider */
    public function testLookup($name, $suite, $module, $expected) {
        $lookup = new TestLookup(codecept_data_dir() . '_fake_app_base_dir');

        $results = $lookup->lookup($name, $suite, $module);
        $this->assertSame(sizeof($expected), sizeof($results));
        foreach ($results as $index => $result) {
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
            'All in module A' => [
                null,
                null,
                'moduleA',
                [
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

}