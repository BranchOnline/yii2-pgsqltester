<?php

namespace branchonline\pgsqltester\filesystem;

use Codeception\Test\Unit;

class TestFileTest extends Unit {

    /** @dataProvider gettersProvider */
    public function testConstructsCorrectInfoFromPath($path, $suite, $module, $index, $runnable) {
        $test_file = new TestFile($path);
        $this->assertSame($path, $test_file->getRelativePath());
        $this->assertSame($suite, $test_file->getSuite());
        $this->assertSame($module, $test_file->getModule());
        $this->assertSame($index, $test_file->getIndex());
        $this->assertSame($runnable, $test_file->isRunnable());
    }

    public function gettersProvider() {
        return [
            ['/tests/ClassATest.php', null, null, 'classa', false],
            ['tests/ClassATest.php', null, null, 'classa', false],
            ['tests/unit/ClassATest.php', 'unit', null, 'classa', true],
            ['tests/integration/ClassATest.php', 'integration', null, 'classa', true],
            ['/common/tests/unit/ClassATest.php', 'unit', 'common', 'classa', true],
            ['common/tests/unit/ClassATest.php', 'unit', 'common', 'classa', true],
        ];
    }

}