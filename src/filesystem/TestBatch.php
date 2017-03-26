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

    /** @var TestRequest The test request that was used to create this batch. */
    private $_request;

    /** @var array Array with all suites required to run this batch. */
    private $_required_suites;

    /** @var array Array with all modules required to run this batch. */
    private $_required_modules;

    /**
     * Construct a new batch of tests.
     *
     * @param TestFile[] $files The tests to be included.
     */
    public function __construct(array $files, TestRequest $request) {
        $this->_files   = $files;
        $this->_request = $request;
        $this->_prepareSuitesAndModules();
    }

    /**
     * Based on the given suite and module and the tests in the batch, determines whether
     * this batch can be run or that more information is required.
     *
     * @return bool Whether this batch can run.
     */
    public function canRun(): bool {
        return !$this->isEmpty() && !$this->hasMultipleFilesToRun() && !$this->hasConflictingModules();
    }

    /** @return array|null The paths of the test to be run, or null if no files require to be specified. */
    public function getFilesToRun() {
        if (!$this->_request->requestsName()) {
            return null;
        }
        $paths = [];
        foreach ($this->_files as $file) {
            $paths[] = $file->getRelativePath();
        }
        return $paths;
    }

    /** @return array|null The name of the suite to be run or null if multiple suites have to be run. */
    public function getSuitesToRun() {
        if (!$this->_request->requestsName() && !$this->_request->requestsSuite()) {
            return null;
        }
        return $this->_required_suites;
    }

    /** @return array|null The name of the module to be run or null if multiple modules have to be run. */
    public function getModulesToRun() {
        if (!$this->_request->requestsName() && !$this->_request->requestsSuite() && !$this->_request->requestsModule()) {
            return null;
        }
        $modules = [];
        foreach ($this->_required_modules as $module) {
            if (strlen($module) > 0) {
                $modules[] = $module;
            }
        }
        return $modules === [] ? null : $modules;
    }

    /** @return bool Whether this batch is empty. */
    public function isEmpty(): bool {
        return [] === $this->_files;
    }

    /** @return bool Whether this batch has multiple files to run. */
    public function hasMultipleFilesToRun(): bool {
        $files_to_run = $this->getFilesToRun();
        return is_array($files_to_run) && sizeof($this->getFilesToRun()) > 1;
    }

    /** @return bool Whether this batch has conflicting modules. */
    public function hasConflictingModules(): bool {
        return $this->_request->requestsSuite() && sizeof($this->_required_modules) > 1;
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
        $this->_required_suites  = array_keys($suites);
        $this->_required_modules = array_keys($modules);
    }
}
