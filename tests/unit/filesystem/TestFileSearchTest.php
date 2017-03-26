<?php

namespace branchonline\pgsqltester\filesystem;

use Codeception\Test\Unit;

/**
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestFileSearchTest extends Unit {

    /** @dataProvider queryMatchesFileProvider */
    public function testQueryMatchesFile($name, $path, $max_string_distance, $expected_match) {
        $actual_match = TestFileSearch::queryMatchesPath($name, $path, $max_string_distance);

        $this->assertSame($expected_match, $actual_match);
    }

    public function queryMatchesFileProvider() {
        return [
            'empty string doesn\'t match' => [
                '',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                0,
                false
            ],
            'lowerclass class name does match' => [
                'classa',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                0,
                true
            ],
            'Correctly cased class name does match' => [
                'ClassA',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                0,
                true
            ],
            'Misspelled lower class name does match with correct distance' => [
                'clasa',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                1,
                true
            ],
            'Misspelled lower class name doesn\'t match with incorrect distance' => [
                'clasa',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                0,
                false
            ],
            'Full correctly cased class name does match' => [
                'ClassATest',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                0,
                true
            ],
            'Full correctly cased class name with extension does match' => [
                'ClassATest.php',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                0,
                true
            ],
            'Full lower cased misspelled class name with correct distance does match' => [
                'clasatest.php',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                1,
                true
            ],
            'Full correctly cased path does match' => [
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                0,
                true
            ],
            'Full lower cased path does match' => [
                'modulea/tests/unit/subsystema/classatest.php',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                0,
                true
            ],
            'Full lower cased path without extension name does match' => [
                'modulea/tests/unit/subsystema/classatest',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                0,
                true
            ],
            'Full lower cased path without extension and test does match' => [
                'modulea/tests/unit/subsystema/classa',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                0,
                true
            ],
            'Partial lower cased path without extension name does match' => [
                'subsystema/classa',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                0,
                true
            ],
            'Partial lower cased wrong path without extension name doesn\'t match' => [
                'subsystemb/classa',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                0,
                false
            ],
            'Partial lower cased wrong path without extension name with more distance doesn\'t match' => [
                'subsystemb/classa',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                1,
                false
            ],
            'Partial lower cased path without extension with misspelled class name with correct distance does match' => [
                'subsystema/clasa',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                1,
                true
            ],
            'Partial lower cased path without extension with misspelled class name with incorrect distance doesn\'t match' => [
                'subsystema/clasa',
                'moduleA/tests/unit/subsystemA/ClassATest.php',
                0,
                false
            ],
        ];
    }

    /** @dataProvider findInIndexProvider */
    public function testFindInIndex(TestRequest $request, $expected) {
        $index = $this->_getFakeAppBaseDirTestIndex();

        $request->setMaxStringDistance(1);

        $matches = TestFileSearch::findInIndex($index, $request);
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
                    static::path('/moduleA/tests/unit/subsystemB/ClassBTest.php'),
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
                    static::path('/moduleA/tests/unit/subsystemB/ClassBTest.php'),
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
                    static::path('/moduleA/tests/unit/subsystemB/ClassBTest.php'),
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