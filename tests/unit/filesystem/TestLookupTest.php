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
    public function testBatchIsCorrect($request, $expected) {
        $lookup = $this->constructLookup();

        $batch = $lookup->lookup($request);

        $this->assertInstanceOf(TestBatch::class, $batch);
        $this->assertSame($expected['can_run'], $batch->canRun());
        $this->assertSame($expected['name'], $batch->getNameToRun());
        $this->assertSame($expected['suite'], $batch->getSuiteToRun());
        $this->assertSame($expected['module'], $batch->getModuleToRun());
    }

    public function canRunProvider() {
        return [
            'All Class B in module A' => [
                new TestRequest('classb', null, 'moduleA'),
                [
                    'can_run' => false,
                    'name'    => false,
                    'suite'   => false,
                    'module'  => false,
                ]
            ],
            'All in suite unit' => [
                new TestRequest(null, 'unit', null),
                [
                    'can_run' => false,
                    'name'    => false,
                    'suite'   => false,
                    'module'  => false,
                ]
            ],
            'All in suite style' => [
                new TestRequest(null, 'style', null),
                [
                    'can_run' => true,
                    'name'    => null,
                    'suite'   => 'style',
                    'module'  => 'moduleA',
                ]
            ],
            'All in module A in suite style' => [
                new TestRequest(null, 'unit', 'moduleA'),
                [
                    'can_run' => true,
                    'name'    => null,
                    'suite'   => 'unit',
                    'module'  => 'moduleA',
                ]
            ],
            'All in module A' => [
                new TestRequest(null, null, 'moduleA'),
                [
                    'can_run' => true,
                    'name'    => null,
                    'suite'   => null,
                    'module'  => 'moduleA',
                ]
            ],
            'All Class A in suite unit in module A' => [
                new TestRequest('classa', 'unit', 'moduleA'),
                [
                    'can_run' => true,
                    'name'    => static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                    'suite'   => 'unit',
                    'module'  => 'moduleA',
                ]
            ],
            'All Class A in suite unit' => [
                new TestRequest('classa', 'unit', null),
                [
                    'can_run' => false,
                    'name'    => false,
                    'suite'   => false,
                    'module'  => false,
                ]
            ],
            'All Class D' => [
                new TestRequest('classd', null, null),
                [
                    'can_run' => true,
                    'name'    => static::path('/tests/unit/subsystemB/ClassDTest.php'),
                    'suite'   => 'unit',
                    'module'  => null,
                ]
            ],
            'All Class A' => [
                new TestRequest('classa', null, null),
                [
                    'can_run' => false,
                    'name'    => false,
                    'suite'   => false,
                    'module'  => false,
                ]
            ],
            'All tests' => [
                new TestRequest(null, null, null),
                [
                    'can_run' => true,
                    'name'    => null,
                    'suite'   => null,
                    'module'  => null,
                ]
            ],
            'All class EEE' => [
                new TestRequest('classeee', null, null),
                [
                    'can_run' => false,
                    'name'    => false,
                    'suite'   => false,
                    'module'  => false,
                ]
            ],
            'All in integration' => [
                new TestRequest(null, 'integration', null),
                [
                    'can_run' => true,
                    'name'    => null,
                    'suite'   => 'integration',
                    'module'  => null,
                ]
            ],
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