<?php

namespace branchonline\pgsqltester\filesystem;

use InvalidArgumentException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use SplFileObject;

/**
 * Indexes Test files within a given directory structure for quick lookup.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestFileIndex {

    /** @var TestFile[] Indexed test files. */
    private $_files = [];

    /**
     * Construct a new test file index relative to a given base directory.
     *
     * @param string $base_dir The base directory for which to construct a test index.
     * @throw InvalidArgumentException When the given directory is not a directory or is not readable.
     */
    public function __construct(string $base_dir) {
        if (!is_dir($base_dir) || !is_readable($base_dir)) {
            throw new InvalidArgumentException('Unknown or unreadable base directory given.');
        }
        $this->_files = $this->_indexFiles($base_dir);
    }

    /** @return TestFile[] The set of TestFile instances indexed by this index. */
    public function getFiles(): array {
        return $this->_files;
    }

    /** @return integer The number of test files indexed by this test file index. */
    public function getNumIndexedFiles() {
        return sizeof($this->_files);
    }

    /**
     * Creates an array of TestFiles relative to the base directory.
     *
     * @param string $base_dir The base directory of the file index.
     * @return TestFile[] The list of test files.
     */
    private function _indexFiles(string $base_dir): array {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $files = [];
        foreach ($iterator as $file){
            /** @var SplFileObject $file */
            if (1 === preg_match('/Test.php$/', $file->getBaseName())) {
                $relative_path = str_replace($base_dir, '', $file->getPathName());
                $test_file = new TestFile($relative_path);
                $files[] = $test_file;
            }
        }

        return $files;
    }

}