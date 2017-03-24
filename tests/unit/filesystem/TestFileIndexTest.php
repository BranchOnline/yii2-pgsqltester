<?php

namespace branchonline\pgsqltester\filesystem;

use Codeception\Test\Unit;
use InvalidArgumentException;

/**
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestFileIndexTest extends Unit {

    public function testInstantiateForUnknownBaseDir() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown or unreadable base directory given.');

        new TestFileIndex('unknown/basedir');
    }

    public function testIndexTestClasses() {
        $base_dir = codecept_data_dir() . '_fake_app_base_dir/tests';
        $index = new TestFileIndex($base_dir);
        $this->assertSame($base_dir, $index->getBaseDir());

        $this->assertSame([
            'classa' => '/subsystemA/ClassATest.php',
            'classb' => '/subsystemA/ClassBTest.php',
            'classc' => '/subsystemB/ClassCTest.php',
            'classd' => '/subsystemB/ClassDTest.php',
        ], $index->getIndexedFiles());
    }

}