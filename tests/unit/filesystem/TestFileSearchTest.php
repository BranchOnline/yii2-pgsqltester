<?php

namespace branchonline\pgsqltester\filesystem;

use Codeception\Test\Unit;

/**
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestFileSearchTest extends Unit {

    /** @dataProvider findInIndexProvider() */
    public function testFindInIndex($request, $expected) {
        $index = $this->_getFakeAppBaseDirTestIndex();

        $matches = TestFileSearch::findInIndex($index, $request, 1);
        $this->assertSame(sizeof($expected), sizeof($matches));
        foreach ($matches as $index => $result) {
            $this->assertSame($result->getRelativePath(), $expected[$index]);
        }
    }

    public function findInIndexProvider() {
        return [
            'All tests' => [
                new TestRequest(null, null, null),
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
                new TestRequest('classeee', null, null),
                []
            ],
            'All Misspelled Class A' => [
                new TestRequest('clasa', null, null),
                [
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                    static::path('/tests/integration/ClassATest.php'),
                    static::path('/tests/unit/subsystemA/ClassATest.php'),
                ]
            ],
            'All Class A' => [
                new TestRequest('classa', null, null),
                [
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                    static::path('/tests/integration/ClassATest.php'),
                    static::path('/tests/unit/subsystemA/ClassATest.php'),
                ]
            ],
            'All in unit' => [
                new TestRequest(null, 'unit', null),
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
            'All in integration' => [
                new TestRequest(null, 'integration', null),
                [
                    static::path('/tests/integration/ClassATest.php'),
                ]
            ],
            'All in module A' => [
                new TestRequest(null, null, 'moduleA'),
                [
                    static::path('/moduleA/tests/style/ClassETest.php'),
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                    static::path('/moduleA/tests/unit/subsystemA/ClassBTest.php'),
                    static::path('/moduleA/tests/unit/subsystemA/ClassCTest.php'),
                ]
            ],
            'All in module B' => [
                new TestRequest(null, null, 'moduleB'),
                [],
            ],
            'All Class A in suite unit' => [
                new TestRequest('classa', 'unit', null),
                [
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                    static::path('/tests/unit/subsystemA/ClassATest.php'),
                ]
            ],
            'All Class A in module A' => [
                new TestRequest('classa', null, 'moduleA'),
                [
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                ]
            ],
            'All in module A in suite style' => [
                new TestRequest(null, 'style', 'moduleA'),
                [
                    static::path('/moduleA/tests/style/ClassETest.php'),
                ]
            ],
            'All Class A in suite unit in module A' => [
                new TestRequest('classa', 'unit', 'moduleA'),
                [
                    static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'),
                ]
            ],
        ];
    }

    private function _getFakeAppBaseDirTestIndex(): TestFileIndex {
        $base_dir = codecept_data_dir() . '_fake_app_base_dir';
        return new TestFileIndex($base_dir);
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