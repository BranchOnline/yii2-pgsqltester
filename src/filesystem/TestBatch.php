<?php

namespace branchonline\pgsqltester\filesystem;

/**
 * Models a collection of tests that should be run at once.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestBatch {

    /** @var TestFile[] The test files in this batch. */
    private $_files;

    /**
     * Construct a new batch of tests.
     *
     * @param TestFile[] $files The tests to be included.
     */
    public function __construct(array $files) {
        $this->_files = $files;
    }

    /** @return int The number of files in this batch */
    public function getSize() {
        return sizeof($this->getFiles());
    }

    /** @return TestFile[] The test files in this batch. */
    public function getFiles(): array {
        return $this->_files;
    }

}
