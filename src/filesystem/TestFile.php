<?php

namespace branchonline\pgsqltester\filesystem;

/**
 * Models a single test file.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestFile {

    /** @var string|null The suite in which this test file resides. Can be null if it is not inside a suite. */
    private $_suite;

    /** @var string|null The module in which this test file resides. Can be null if it is not inside a module. */
    private $_module = null;

    /** @var string The relative path to the test file. */
    private $_relative_path;

    /** @var string The index of this test file. */
    private $_index;

    /**
     * Construct a new test file.
     *
     * @param string $path The path to the test file, relative to the application base directory.
     */
    public function __construct(string $relative_path) {
        $this->_relative_path = $relative_path;
        $this->_extractPathData();
    }

    /** Extract the suite from the path. */
    private function _extractPathData() {
        $parts                = explode(DIRECTORY_SEPARATOR, $this->_relative_path);
        $full_name            = array_pop($parts);
        $this->_index         = $this->_buildIndexFromName($full_name);
        list($suite, $module) = $this->_extractSuiteAndModule($parts);
        $this->_suite         = $suite;
        $this->_module        = $module;
    }

    /**
     * Extract the suite and module from the given path parts.
     *
     * @param string[] $parts The path parts.
     * @return array Returns a list with two elements, the first being the suite, the second the module.
     */
    private function _extractSuiteAndModule(array $parts) {
        $suite  = null;
        $module = null;
        foreach ($parts as $index => $part) {
            if ($part === 'tests') {
                $suite  = $parts[$index + 1] ?? null;
                $module = $parts[$index - 1] ?? null;
                break;
            }
        }
        if ($suite === '') {
            $suite = null;
        }
        if ($module === '') {
            $module = null;
        }
        return [$suite, $module];
    }

    /**
     * Build the file index from the full name.
     *
     * @param string $full_name The full file name.
     * @return string The file index.
     */
    private function _buildIndexFromName(string $full_name): string {
        return strtolower(preg_replace('/Test.php$/', '', $full_name));
    }

    /** @return bool Whether this test file is runnable. */
    public function isRunnable(): bool {
        return $this->_suite !== null;
    }

    /** @return string The index of this test file. */
    public function getIndex(): string {
        return $this->_index;
    }

    /** @return null|string The suite of this test file. */
    public function getSuite() {
        return $this->_suite;
    }

    /** @return null|string The module of this test file, null if the test file is not in any module. */
    public function getModule() {
        return $this->_module;
    }

    /** @return string The path to the test file, relative to the application base dir. */
    public function getRelativePath(): string {
        return $this->_relative_path;
    }

}