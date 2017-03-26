<?php

namespace branchonline\pgsqltester\filesystem;

use Codeception\Test\Unit;

class TestFileTest extends Unit {

    /** @dataProvider gettersProvider */
    public function testConstructsCorrectInfoFromPath($path, $suite, $module) {
        $test_file = new TestFile($path);
        $this->assertSame($path, $test_file->getRelativePath());
        $this->assertSame($suite, $test_file->getSuite());
        $this->assertSame($module, $test_file->getModule());
    }

    public function gettersProvider() {
        return [
            [static::path('/tests/ClassATest.php'), null, null],
            [static::path('tests/ClassATest.php'), null, null],
            [static::path('tests/unit/ClassATest.php'), 'unit', null],
            [static::path('tests/integration/ClassATest.php'), 'integration', null],
            [static::path('/common/tests/unit/ClassATest.php'), 'unit', 'common'],
            [static::path('common/tests/unit/ClassATest.php'), 'unit', 'common'],
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