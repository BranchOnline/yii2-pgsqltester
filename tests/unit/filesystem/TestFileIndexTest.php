<?php

namespace branchonline\pgsqltester\filesystem;

use Codeception\Test\Unit;
use InvalidArgumentException;

/**
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestFileIndexTest extends Unit {

    public function testIndexUnknownBaseDir() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown or unreadable base directory given.');

        new TestFileIndex('unknown/basedir');
    }

    public function testMatchAnySubdirConstant() {
        $this->assertSame('*' . DIRECTORY_SEPARATOR, TestFileIndex::MATCH_ANY_SUBDIR);
    }

    public function testNumIndexedFiles() {
        $index = $this->_getFakeAppBaseDirTestIndex();

        $expected_files = [
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
        ];

        $this->_assertIndexMatches($expected_files, $index);
    }

    public function testExcludeAllWithSubsystemA() {
        $index = $this->_getFakeAppBaseDirTestIndex();
        $index->setExcludeDirs(['*/subsystemA']);

        $expected_files = [
            static::path('/moduleA/tests/style/ClassETest.php'),
            static::path('/moduleA/tests/unit/subsystemB/ClassBTest.php'),
            static::path('/tests/integration/ClassATest.php'),
            static::path('/tests/unit/subsystemB/ClassCTest.php'),
            static::path('/tests/unit/subsystemB/ClassDTest.php'),
        ];
        $this->_assertIndexMatches($expected_files, $index);
    }

    public function testExcludeModuleA() {
        $index = $this->_getFakeAppBaseDirTestIndex();
        $index->setExcludeDirs(['/moduleA']);

        $expected_files = [
            static::path('/tests/integration/ClassATest.php'),
            static::path('/tests/unit/subsystemA/ClassATest.php'),
            static::path('/tests/unit/subsystemA/ClassBTest.php'),
            static::path('/tests/unit/subsystemB/ClassCTest.php'),
            static::path('/tests/unit/subsystemB/ClassDTest.php'),
        ];
        $this->_assertIndexMatches($expected_files, $index);
    }

    public function testExcludeAll() {
        $index = $this->_getFakeAppBaseDirTestIndex();
        $index->setExcludeDirs(['/moduleA', 'tests']);

        $expected_files = [];
        $this->_assertIndexMatches($expected_files, $index);
    }

    private function _assertIndexMatches($expected_files, TestFileIndex $index) {
        $actual_files = $index->getFiles();
        $this->assertSame(sizeof($expected_files), sizeof($actual_files));
        foreach ($actual_files as $idx => $file) {
            $this->assertInstanceOf(TestFile::class, $file);
            $this->assertSame($expected_files[$idx], $file->getRelativePath());
        }
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