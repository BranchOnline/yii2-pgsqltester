<?php

namespace branchonline\pgsqltester\filesystem;

/**
 * Search based on a given file index and request.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class TestFileSearch {

    /**
     * Find a selection of files based on a given request.
     *
     * Broadens the maximum string distance and returns the first set containing any files or an empty set if the
     * given maximum is reached.
     *
     * @param TestFileIndex $index               The index to search through.
     * @param TestRequest   $request             Specifying the request data.
     * @return TestFile[] Array with matching files.
     */
    public static function findInIndex(TestFileIndex $index, TestRequest $request): array {
        $matches             = [];
        $string_distance     = 0;
        $max_string_distance = $request->getMaxStringDistance();
        while ($string_distance <= $max_string_distance) {
            foreach ($index->getFiles() as $file) {
                if ($request->requestsName() && !static::_stringsMatch($file->getIndex(), $request->getName(), $string_distance)) {
                    continue;
                }
                if ($request->requestsSuite() && !($file->getSuite() === $request->getSuite())) {
                    continue;
                }
                if ($request->requestsModule() && !($file->getModule() === $request->getModule())) {
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
    private static function _stringsMatch(string $string1, string $string2, int $max_string_distance = 0) {
        if (0 === $max_string_distance) {
            return $string1 === $string2;
        } else {
            return levenshtein($string1, $string2) <= $max_string_distance;
        }
    }

}