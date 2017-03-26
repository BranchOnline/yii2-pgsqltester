<?php

namespace branchonline\pgsqltester\filesystem;

use InvalidArgumentException;

/**
 * Models the request for a particular test or group of tests.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestRequest {

    /** @var string|null the requested name. Null if request by name is not required. */
    private $_name;

    /** @var string|null The requested suite. Null if request by suite is not required. */
    private $_suite;

    /** @var string|null the requested module. Null if request by module is not required. */
    private $_module;

    /** @var int The maximum levenshtein distance allowed to allow fuzzy matching. */
    private $_max_string_distance = 0;

    /**
     * Construct a new test request.
     *
     * @param string|null $name   Optionally specify the test name.
     * @param string|null $suite  Optionally specify the test suite.
     * @param string|null $module Optionally specify the test suite.
     */
    public function __construct($name = null, $suite = null, $module = null) {
        $this->_name = $name;
        $this->_suite = $suite;
        $this->_module = $module;
    }

    /** @param int $max_string_distance Specify the string matching distance for this request. */
    public function setMaxStringDistance(int $max_string_distance) {
        if ($max_string_distance < 0) {
            throw new InvalidArgumentException('Max string distance should be a non-negative integer.');
        }
        $this->_max_string_distance = $max_string_distance;
    }

    /** @param string|null $suite Specify the suite. */
    public function setSuite($suite = null) {
        $this->_suite = $suite;
    }

    /** @param string|null $module Specify the module. */
    public function setModule($module = null) {
        $this->_module = $module;
    }

    /** @param string|null $name Specify the test name. */
    public function setName($name = null) {
        $this->_name = $name;
    }

    /** @return bool Whether this is a request for a particular name. */
    public function requestsName(): bool {
        return is_string($this->_name);
    }

    /** @return bool Whether this is a request for a particular suite. */
    public function requestsSuite() {
        return is_string($this->_suite);
    }

    /** @return bool Whether this is a request for a particular module. */
    public function requestsModule() {
        return is_string($this->_module);
    }

    /** @return string|null The requested name. */
    public function getName() {
        return $this->_name;
    }

    /** @return string|null The requested suite. */
    public function getSuite() {
        return $this->_suite;
    }

    /** @return string|null The requested module. */
    public function getModule() {
        return $this->_module;
    }

    /** @return int The maximum string matching distance allowed. */
    public function getMaxStringDistance(): int {
        return $this->_max_string_distance;
    }

}
