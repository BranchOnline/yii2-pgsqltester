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
            $this->_files = $this->_indexFiles();
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
     * @param string $base_dir     The base directory of the file index.
     * @param array  $exclude_dirs List of directories to be excluded from the index.
     * @return TestFile[] The list of test files.
     */
    private function _indexFiles(): array {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->_base_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $files = [];
        foreach ($iterator as $file){
            /** @var SplFileInfo $file */
            if ($this->_isPathExcluded($file->getPath())) {
                continue;
            }
            if (1 === preg_match('/Test.php$/', $file->getBaseName())) {
                $relative_path = str_replace($this->_base_dir, '', $file->getPathName());
                $test_file = new TestFile($relative_path);
                $files[] = $test_file;
            }
        }

        usort($files, function($a, $b) {
            return $a->getRelativePath() <=> $b->getRelativePath();
        });

        return $files;
    }

    /**
     * Determine whether the given file is in one of the exlcuded directories.
     *
     * @param string $path The path to be verified.
     * @return bool Whether the path is excluded.
     */
    private function _isPathExcluded(string $path): bool {
        if ($this->_exclude_dirs === []) {
            return false;
        }
        $file_path = ltrim(str_replace($this->_base_dir, '', $path), DIRECTORY_SEPARATOR);
        foreach ($this->_exclude_dirs as $exclude_dir) {
            if (StringUtil::startsWith($exclude_dir, static::MATCH_ANY_SUBDIR)) {
                $actual_dir = ltrim($exclude_dir, static::MATCH_ANY_SUBDIR);
                if (StringUtil::contains($file_path, $actual_dir)) {
                    return true;
                }
            } elseif (StringUtil::startsWith($file_path, $exclude_dir)) {
                return true;
            }
        }
        return false;
    }

}
