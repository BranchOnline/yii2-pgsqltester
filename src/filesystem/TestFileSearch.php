<?php

namespace branchonline\pgsqltester\filesystem;
use branchonline\pgsqltester\utils\StringUtil;

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
                if ($request->requestsName() && !static::queryMatchesPath($request->getName(), $file->getRelativePath(), $string_distance)) {
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
     * Check whether a given query string (name/path) matches the given path.
     *
     * @param string $query The query string.
     * @param string $path  The path to be matched.
     * @param int $max_distance The maximum distance allowed between the file names of the query and the path. Defaults
     * to 0 meaning an exact match.
     * @return bool Whether the query matches the path.
     */
    public static function queryMatchesPath(string $query, string $path, int $max_distance = 0): bool {
        $cleaned_query = preg_replace('/test(.php)?$/', '', strtolower($query));
        $query_parts   = explode(DIRECTORY_SEPARATOR, $cleaned_query);
        $query_name    = array_pop($query_parts);

        $cleaned_file  = preg_replace('/test(.php)?$/', '', strtolower($path));
        $file_parts    = explode(DIRECTORY_SEPARATOR, $cleaned_file);
        $file_name     = array_pop($file_parts);

        $name_matches  = levenshtein($query_name, $file_name) <= $max_distance;

        $query_path    = implode(DIRECTORY_SEPARATOR, $query_parts);
        $file_path     = implode(DIRECTORY_SEPARATOR, $file_parts);

        $path_matches  = $query_path === '' || StringUtil::endsWith($file_path, $query_path);

        return $name_matches && $path_matches;
    }

}