<?php

namespace branchonline\pgsqltester\filesystem;

/**
 * Object to search over arrays of test files.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestFileSearch {

    /** @var int The maximum allowed levenshtein distance. */
    private $_max_distance = 0;

    /** @var string The string to be matched by the file index. */
    private $_match_string = '';

    /** @var string|null The suite to be matched by the file. NULL if no restriction on suite applies. */
    private $_suite = null;

    /** @var string|null The module to be matched by the file. NULL if no restriction on module applies. */
    private $_module = null;

    /**
     * Make the search match files with the given name.
     *
     * @param string $name                The name to be matched.
     * @param int    $max_string_distance The max string distance, default to 0 which is an exact match.
     * @return self
     */
    public function matches(string $name, int $max_string_distance = 0): self {
        $this->_match_string = $name;
        $this->_max_distance = $max_string_distance;
        return $this;
    }

    /**
     * Only find test files in the specified suite.
     *
     * @param string $suite The suite to be matched.
     * @return self
     */
    public function inSuite(string $suite): self {
        $this->_suite = $suite;
        return $this;
    }

    /**
     * Only find test files in the specified module.
     *
     * @param string $module The module to be matched.
     * @return self
     */
    public function inModule(string $module): self {
        $this->_module = $module;
        return $this;
    }

    /**
     * Find a selection of files.
     *
     * @param TestFileIndex $index The index to search through.
     * @return TestFile[] Array with matching files.
     */
    public function findInIndex(TestFileIndex $index): array {
        $matches         = [];
        $string_distance = 0;
        while ($string_distance <= $this->_max_distance) {
            foreach ($index->getFiles() as $file) {
                if (!$this->_stringsMatch($file->getIndex(), $this->_match_string, $string_distance)) {
                    continue;
                }
                if (is_string($this->_suite) && !($file->getSuite() === $this->_suite)) {
                    continue;
                }
                if (is_string($this->_module) && !($file->getModule() === $this->_module)) {
                    continue;
                }
                $matches[] = $file;
            }
            if ($matches !== []) {
                break;
            }
            $string_distance++;
        }
        return $matches;
    }

    /**
     * Function to check whether two strings match within a max string distance boundary.
     *
     * @param string $string1             The first string.
     * @param string $string2             The second string.
     * @param int    $max_string_distance The maximum allowed string distance.
     * @return bool Whether the strings match.
     */
    private function _stringsMatch(string $string1, string $string2, int $max_string_distance = 0) {
        if (0 === $max_string_distance) {
            return $string1 === $string2;
        } else {
            return levenshtein($string1, $string2) <= $max_string_distance;
        }
    }

}