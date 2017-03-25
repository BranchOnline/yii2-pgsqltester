<?php

namespace branchonline\pgsqltester\filesystem;

use InvalidArgumentException;

/**
 * TestLookup combines lower level filesystem classes to intelligently look up
 * test classes.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestLookup {

    /** @var TestFileIndex Index used for looking up test files. */
    private $_index;

    /**
     * Construct a new test lookup instance.
     *
     * @param string $base_path The base path of the project in which to lookup tests.
     * @throws InvalidArgumentException Whenever the base path is invalid.
     */
    public function __construct(string $base_path) {
        try {
            $this->_index = new TestFileIndex($base_path);
        } catch (InvalidArgumentException $ex) {
            throw new InvalidArgumentException('Invalid base path given.');
        }
    }

    /**
     * Lookup a particular test or set of tests.
     *
     * @param TestRequest $request The request specifying the test(s) to be found.
     * @return TestBatch The test batch to be executed.
     */
    public function lookup(TestRequest $request): TestBatch {
        $test_files = TestFileSearch::findInIndex($this->_index, $request, 1);
        return new TestBatch($test_files, $request);
    }

}
