<?php

namespace branchonline\pgsqltester\filesystem;

use Codeception\Test\Unit;

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
