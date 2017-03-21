<?php

use branchonline\pgsqltester\TestController;
use Codeception\Test\Unit;

class TestControllerTest extends Unit {

    public function testDefaults() {
        $test_controller = $this->setupController();
        $this->assertSame('testing_template', $test_controller->testing_template_db);
        $this->assertSame('testing', $test_controller->testing_db);
        $this->assertSame('@console/migrations/', $test_controller->migration_path);
        $this->assertSame('', $test_controller->suite);
        $this->assertSame('', $test_controller->for_module);
        $this->assertSame(false, $test_controller->coverage);
        $this->assertSame(false, $test_controller->silent);
    }

    public function testOptionsAvailable() {
        $test_controller = $this->setupController();
        $this->assertSame(['suite', 'for_module', 'coverage', 'silent'], $test_controller->options('run'));
    }

    public function testConfigDbInvalid() {
        Yii::$app->set('config_db', null);
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('No database component configured for \'config_db\'.');
        $this->setupController();
    }

    public function testDbInvalid() {
        Yii::$app->set('db', null);
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('No database component configured for \'db\'.');
        $this->setupController();
    }

    protected function setupController() {
        return new TestController('test', Yii::$app);
    }

}