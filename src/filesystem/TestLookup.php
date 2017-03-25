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
     * @param string|null $test_name   The name of a test to look up, or null if no
     * specific test is required.
     * @param string|null $test_suite  The suite name or null if no suite selected.
     * @param string|null $test_module The module name or null if no module selected.
     * @return TestBatch The test batch to be executed.
     */
    public function lookup($test_name = null, $test_suite = null, $test_module = null): TestBatch {
        $search = new TestFileSearch();

        if (is_string($test_name)) {
            $search->matches($test_name, 1);
        }

        if (is_string($test_suite)) {
            $search->inSuite($test_suite);
        }

        if (is_string($test_module)) {
            $search->inModule($test_module);
        }

        $test_files = $search->findInIndex($this->_index);

        return new TestBatch($test_files);
    }

}
