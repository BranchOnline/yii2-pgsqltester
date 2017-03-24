<?php

namespace branchonline\pgsqltester\filesystem;

use Codeception\Test\Unit;

/**
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestFileSearchTest extends Unit {

    public function testFindInIndex() {
        $index = $this->_getFakeAppBaseDirTestIndex();

        $matches = (new TestFileSearch())
            ->matches('classd')
            ->findInIndex($index);
        $this->assertSame(1, sizeof($matches));
        $this->assertSame(static::path('/tests/unit/subsystemB/ClassDTest.php'), $matches[0]->getRelativePath());

        $matches = (new TestFileSearch())
            ->matches('classb')
            ->findInIndex($index);
        $this->assertSame(2, sizeof($matches));
        $this->assertSame(static::path('/moduleA/tests/unit/subsystemA/ClassBTest.php'), $matches[0]->getRelativePath());
        $this->assertSame(static::path('/tests/unit/subsystemA/ClassBTest.php'), $matches[1]->getRelativePath());

        $matches = (new TestFileSearch())
            ->matches('classa')
            ->findInIndex($index);
        $this->assertSame(3, sizeof($matches));
        $this->assertSame(static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'), $matches[0]->getRelativePath());
        $this->assertSame(static::path('/tests/integration/ClassATest.php'), $matches[1]->getRelativePath());
        $this->assertSame(static::path('/tests/unit/subsystemA/ClassATest.php'), $matches[2]->getRelativePath());
    }

    public function testFindFuzzyInIndex() {
        $index = $this->_getFakeAppBaseDirTestIndex();

        $matches = (new TestFileSearch())
            ->matches('clasd', 0)
            ->findInIndex($index);
        $this->assertSame([], $matches);

        $matches = (new TestFileSearch())
            ->matches('clasd', 1)
            ->findInIndex($index);
        $this->assertSame(1, sizeof($matches));
        $this->assertSame(static::path('/tests/unit/subsystemB/ClassDTest.php'), $matches[0]->getRelativePath());

        $matches = (new TestFileSearch())
            ->matches('classd', 1)
            ->findInIndex($index);
        $this->assertSame(1, sizeof($matches));
        $this->assertSame(static::path('/tests/unit/subsystemB/ClassDTest.php'), $matches[0]->getRelativePath());
    }

    public function testFindWithSuiteInIndex() {
        $index = $this->_getFakeAppBaseDirTestIndex();

        $matches = (new TestFileSearch())
            ->matches('classa')
            ->inSuite('unit')
            ->findInIndex($index);

        $this->assertSame(2, sizeof($matches));
        $this->assertSame(static::path('/moduleA/tests/unit/subsystemA/ClassATest.php'), $matches[0]->getRelativePath());
        $this->assertSame(static::path('/tests/unit/subsystemA/ClassATest.php'), $matches[1]->getRelativePath());

        $matches = (new TestFileSearch())
            ->matches('classa')
            ->inSuite('integration')
            ->findInIndex($index);

        $this->assertSame(1, sizeof($matches));
        $this->assertSame(static::path('/tests/integration/ClassATest.php'), $matches[0]->getRelativePath());
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