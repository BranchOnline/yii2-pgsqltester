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

    /** @var bool Whether the name of the testing files was specified. */
    private $_name_is_specified;

    /** @var bool Whether the suite of the testing files was specified. */
    private $_suite_is_specified;

    /** @var array Array with all suites required to run this batch. */
    private $_required_suites;

    /** @var array Array with all modules required to run this batch. */
    private $_required_modules;

    /**
     * Construct a new batch of tests.
     *
     * @param TestFile[] $files The tests to be included.
     */
    public function __construct(array $files, $is_name_specified, $is_suite_specified) {
        $this->_files = $files;
        $this->_name_is_specified = $is_name_specified;
        $this->_suite_is_specified = $is_suite_specified;
        $this->_prepareSuitesAndModules();
    }

    /**
     * Based on the given suite and module and the tests in the batch, determines whether
     * this batch can be run or that more information is required.
     *
     * @return bool Whether this batch can run.
     */
    public function canRun(): bool {
        if ($this->isEmpty()) {
            return false;
        }
        if ($this->_name_is_specified && ($this->hasMultipleRequiredSuites() || $this->hasMultipleRequiredModules())) {
            return false;
        }
        if ($this->_suite_is_specified && ($this->hasMultipleRequiredModules())) {
            return false;
        }
        return true;
    }

    /** @return string|null|false The name of the test to be run or null if multiple test have to be run. False if the batch cannot be run. */
    public function getNameToRun() {
        if (!$this->canRun()) {
            return false;
        }

        if (!$this->_name_is_specified || !isset($this->_files[0])) {
            return null;
        } else {
            return $this->_files[0]->getRelativePath();
        }
    }

    /** @return string|null The name of the suite to be run or null if multiple suites have to be run. */
    public function getSuiteToRun() {
        if (!$this->canRun()) {
            return false;
        }

        if ($this->hasMultipleRequiredSuites()) {
            return null;
        } else {
            return $this->_requiredSuites()[0] ?? null;
        }
    }

    /** @return string|null The name of the module to be run or null if multiple modules have to be run. */
    public function getModuleToRun() {
        if (!$this->canRun()) {
            return false;
        }

        if ($this->hasMultipleRequiredModules()) {
            return null;
        } else {
            $module = $this->_requiredModules()[0] ?? '';
            return $module === '' ? null : $module;
        }
    }

    /** @return bool Whether this batch has multiple required modules. */
    public function hasMultipleRequiredModules() {
        return sizeof($this->_requiredModules()) > 1;
    }

    /** @return bool Whether this batch has multiple required suites. */
    public function hasMultipleRequiredSuites() {
        return sizeof($this->_requiredSuites()) > 1;
    }

    /** @return string[] The required suite names */
    private function _requiredSuites() {
        return array_keys($this->_required_suites);
    }

    /** @return string[] The required module names */
    private function _requiredModules() {
        return array_keys($this->_required_modules);
    }

    /** @return bool Whether this batch is empty. */
    public function isEmpty(): bool {
        return [] === $this->_files;
    }

    /** @return int The number of files in this batch */
    public function getSize(): int {
        return sizeof($this->getFiles());
    }

    /** @return TestFile[] The test files in this batch. */
    public function getFiles(): array {
        return $this->_files;
    }

    /** Determine the set of all suits and all modules for this batch. Grouped so it allows a single for-loop. */
    private function _prepareSuitesAndModules() {
        $suites  = [];
        $modules = [];
        foreach ($this->_files as $file) {
            $suite = $file->getSuite();
            if (!empty($suite) && !isset($suites[$suite])) {
                $suites[$suite] = true;
            }
            $module = $file->getModule();
            if (!isset($modules[$module])) {
                $modules[$module] = true;
            }
        }
        $this->_required_suites  = $suites;
        $this->_required_modules = $modules;
    }
}