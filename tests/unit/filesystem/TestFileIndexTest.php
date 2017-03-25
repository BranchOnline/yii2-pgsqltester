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

    public function testNumIndexedFiles() {
        $indexed_files = $this->_getFakeAppBaseDirTestIndex();

        $this->assertSame(10, $indexed_files->getNumIndexedFiles());
        foreach ($indexed_files->getFiles() as $file) {
            $this->assertInstanceOf(TestFile::class, $file);
        }
    }

    private function _getFakeAppBaseDirTestIndex(): TestFileIndex {
        $base_dir = codecept_data_dir() . '_fake_app_base_dir';
        return new TestFileIndex($base_dir);
    }

}