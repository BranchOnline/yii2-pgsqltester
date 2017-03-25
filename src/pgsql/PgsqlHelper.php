<?php

namespace branchonline\pgsqltester\pgsql;

/**
 * Helper class that constructs postgres specific SQL and commands.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class PgsqlHelper {

    /** @return string The SQL statement. */
    public static function getDropDatabaseIfExistsSql(string $db_name) {
        return 'DROP DATABASE IF EXISTS ' . $db_name;
    }

    /** @return string The SQL statement. */
    public static function getCreateDatabaseSql(string $db_name) {
        return 'CREATE DATABASE ' . $db_name;
    }

}
