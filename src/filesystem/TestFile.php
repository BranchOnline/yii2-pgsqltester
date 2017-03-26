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

        array_pop($parts);

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