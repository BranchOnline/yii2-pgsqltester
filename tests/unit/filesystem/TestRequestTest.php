<?php

namespace branchonline\pgsqltester\filesystem;

use Codeception\Test\Unit;
use InvalidArgumentException;

/**
 * Description of TestRequestTest
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestRequestTest extends Unit {

    /** @dataProvider gettersProvider */
    public function testGetters($name, $suite, $module) {
        $request = new TestRequest($name, $suite, $module);

        $this->assertSame($name, $request->getName());
        $this->assertSame($name !== null, $request->requestsName());

        $this->assertSame($suite, $request->getSuite());
        $this->assertSame($suite !== null, $request->requestsSuite());

        $this->assertSame($module, $request->getModule());
        $this->assertSame($module !== null, $request->requestsModule());
    }

    public function testNegativeMaxStringDistance() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max string distance should be a non-negative integer.');

        $request = new TestRequest();
        $request->setMaxStringDistance(-1);
    }

    public function testMaxStringDistance() {
        $request = new TestRequest();
        $this->assertSame(0, $request->getMaxStringDistance());
        $request->setMaxStringDistance(1);
        $this->assertSame(1, $request->getMaxStringDistance());
    }

    public function gettersProvider() {
        return [
            [null, null, null],
            ['classa', null, null],
            [null, 'unit', null],
            [null, null, 'moduleA'],
            ['classa', 'unit', null],
            [null, 'unit', 'moduleA'],
            ['classa', 'unit', 'moduleA'],
        ];
    }

}
