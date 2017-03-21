<?php

namespace branchonline\pgsqltester\filesystem;

use InvalidArgumentException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use SplFileObject;

/**
 * Indexes and holds Test files within a given directory structure for quick lookup.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestFileIndex {

    /** @var string $base_dir Holds the path to the base directory. */
    private $_base_dir;

    /** @var array Holds the indexed files.  */
    private $_indexed_files = [];

    /**
     * Construct new file index relative to the given base directory.
     *
     * @param string $base_dir The base directory of the file index.
     */
    public function __construct(string $base_dir) {
        if (!is_dir($base_dir) || !is_readable($base_dir)) {
            throw new InvalidArgumentException('Unknown or unreadable base directory given.');
        }
        $this->_base_dir = $base_dir;
        $this->reindex();
    }

    /**
     * Reindex this instance by redoing the indexing calculation.
     *
     * @return void
     */
    public function reindex() {
        $iterator = new \RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->_base_dir,
                RecursiveDirectoryIterator::SKIP_DOTS
            )
        );

        $files = [];
        foreach ($iterator as $file){
            /** @var SplFileObject $file */
            if (1 === preg_match('/Test.php$/', $file->getBaseName())) {
                $test_name     = strtolower(preg_replace('/Test.php/', '', $file->getBaseName()));
                $relative_path = str_replace($this->_base_dir, '', $file->getPathName());
                $files[$test_name] = $relative_path;
            }
        }

        ksort($files);

        $this->_indexed_files = $files;
    }

    /** @return array An array of the indexed files. */
    public function getIndexedFiles(): array {
        return $this->_indexed_files;
    }

    /** @return string The base directory. */
    public function getBaseDir(): string {
        return $this->_base_dir;
    }

}