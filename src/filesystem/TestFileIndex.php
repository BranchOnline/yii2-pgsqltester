<?php

namespace branchonline\pgsqltester\filesystem;

use branchonline\pgsqltester\utils\StringUtil;
use InvalidArgumentException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use SplFileInfo;

/**
 * Indexes Test files within a given directory structure for quick lookup.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestFileIndex {

    /** @var TestFile[]|null Indexed test files. Null if the test files should be reindexed. */
    private $_files;

    /** @var string The base directory of this index.  */
    private $_base_dir;

    /** @var array A list of directories to be excluded relative to the base directory. */
    private $_exclude_dirs = [];

    /** @var string the wildcard specifying to match any subdirectory. */
    const MATCH_ANY_SUBDIR = '*' . DIRECTORY_SEPARATOR;

    /**
     * Construct a new test file index relative to a given base directory.
     *
     * @param string $base_dir     The base directory for which to construct a test index.
     * @param array  $exclude_dirs List of directories to be excluded from the index.
     * @throw InvalidArgumentException When the given directory is not a directory or is not readable.
     */
    public function __construct(string $base_dir) {
        if (!is_dir($base_dir) || !is_readable($base_dir)) {
            throw new InvalidArgumentException('Unknown or unreadable base directory given.');
        }
        $this->_base_dir     = $base_dir;
    }

    /**
     * Set the directories that will be excluded.
     *
     * @param array $exclude_dirs A list of directories to be excluded relative to the base directory.
     * @return void
     */
    public function setExcludeDirs(array $exclude_dirs) {
        $trimmed_excluded_dirs = [];
        foreach ($exclude_dirs as $exclude_dir) {
            $trimmed_excluded_dirs[] = ltrim($exclude_dir, DIRECTORY_SEPARATOR);
        }
        $this->_exclude_dirs = $trimmed_excluded_dirs;
        $this->clearIndex();
    }

    /** @return TestFile[] The set of TestFile instances indexed by this index. */
    public function getFiles(): array {
        if (!is_array($this->_files)) {
            $this->_files = $this->_indexFiles($this->_base_dir);
        }
        return $this->_files;
    }

    /**
     * Clears the index so it will be rebuilt on the next call to [[getFiles()]].
     *
     * @return void
     */
    public function clearIndex() {
        $this->_files = null;
    }

    /**
     * Creates an array of TestFiles relative to the base directory.
     *
     * @param string $dir The base directory of the file index.
     * @return TestFile[] The list of test files.
     */
    private function _indexFiles(string $dir): array {
        $directory = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $filter = new TestDirFilter($directory, $this->_exclude_dirs);
        $filtered_iterator = new RecursiveIteratorIterator($filter);

        foreach ($filtered_iterator as $file) {
            $relative_path = str_replace($this->_base_dir, '', $file->getPathName());
            $test_file = new TestFile($relative_path);
            $files[] = $test_file;
        }

        usort($files, function($a, $b) {
            return $a->getRelativePath() <=> $b->getRelativePath();
        });

        return $files;
    }

}
