<?php

namespace branchonline\pgsqltester\pgsql;

use Codeception\Test\Unit;

class PgsqlHelperTest extends Unit {

    public function testGetDropDatabaseIfExistsSql() {
        $sql = PgsqlHelper::getDropDatabaseIfExistsSql('databasename');
        $this->assertSame('DROP DATABASE IF EXISTS databasename', $sql);
    }

    public function testGetCreateDatabaseSql() {
        $sql = PgsqlHelper::getCreateDatabaseSql('databasename');
        $this->assertSame('CREATE DATABASE databasename', $sql);
    }

}
