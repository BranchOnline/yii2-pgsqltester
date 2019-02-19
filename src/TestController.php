<?php

namespace branchonline\pgsqltester;

use branchonline\pgsqltester\cmd\BuildCommandConstructor;
use branchonline\pgsqltester\cmd\RunCommandConstructor;
use branchonline\pgsqltester\filesystem\TestBatch;
use branchonline\pgsqltester\filesystem\TestFileIndex;
use branchonline\pgsqltester\filesystem\TestLookup;
use branchonline\pgsqltester\filesystem\TestRequest;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\console\Controller;
use yii\console\controllers\MigrateController;
use yii\db\Exception;
use yii\db\Query;
use Yii;
use yii\helpers\Console;

/**
 * The test controller is used to set up a new empty testing database and run tests on it. This guarantees that the
 * database the tests are running on is always in the correct state.
 *
 * Furthermore the controller wraps the default codecept syntax, providing easy access to any test available.
 *
 * Warning: This class is designed to function with PostgreSQL only.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 * @version v2.0
 */
class TestController extends Controller {

    /** @var string Special keyword to run all suites. */
    const ALL_SUITES = 'ALL';

    /** @var string The name of the testing template database on which the migrations will be run. */
    public $testing_template_db = 'testing_template';

    /** @var string The name of the testing database on which the tests will be run. */
    public $testing_db = 'testing';

    /** @var string The path to the migrations folder. */
    public $migration_path = '@console/migrations/';

    /** @var string The codeception suite to run the tests from. Use the special keyword ALL to run all tests for a
     * particular module. */
    public $suite = '';

    /** @var string The system module to run the tests for. */
    public $for_module = '';

    /** @var string The type of coverage to output, by default outputs no coverage. */
    public $coverage = false;

    /** @var bool Whether to run in silent mode (no output will be shown) */
    public $silent = false;

    /** @inheritdoc */
    public function options($actionID) {
        switch ($actionID) {
            case 'run':
                return ['suite', 'for_module', 'coverage', 'silent'];
            case 'build':
                return ['silent'];
            default:
                return [];
        }
    }

    /** @inheritdoc */
    public function optionAliases() {
        return ['m' => 'for_module', 's' => 'suite', 'c' => 'coverage'];
    }

    /**
     * Setup the controller.
     *
     * Contains built-in safety to make sure this controller cannot be used outside of a test environment.
     *
     * @throws InvalidCallException Whenever this controller is initialized outside a test environment.
     */
    public function init() {
        parent::init();
        if (!YII_ENV_TEST) {
            throw new InvalidCallException('TestController actions should only be called via test environment!');
        }
        if (!Yii::$app->has('config_db')) {
            throw new InvalidConfigException('No database component configured for \'config_db\'.');
        }
        if (!Yii::$app->has('db')) {
            throw new InvalidConfigException('No database component configured for \'db\'.');
        }
        $this->defaultAction = 'run';
    }

    /**
     * Build the required classes for the different test suites.
     *
     * @return void
     */
    public function actionBuild() {
        print_r("Building required classes\n");
        $command_string = (new BuildCommandConstructor($this->silent))->getCommand();
        passthru($command_string);
    }

    /**
     * Runs the tests according to the given specifications.
     *
     * Also migrates the test database to the correct state if required.
     *
     * @param string $test_class    The name of the test class that you want to run. You are allowed to use the
     * model name directly if it can be unambiguously resolved to a class path.
     * @param string $test_function The name of the test function that you want to run.
     * @return void
     */
    public function actionRun($test_class = '', $test_function = '') {
        if ($this->actionPrepareDb()) {
            $lookup      = $this->_createTestLookup();
            $request     = $this->_createTestRequest($test_class, $this->suite, $this->for_module);
            $constructor = $this->runInteractiveTestRequest($lookup, $request);
            if ($constructor instanceof RunCommandConstructor) {
                $constructor->setFunction($test_function);
                $command = $constructor->getCommand();
                print_r('About to run \'' . $command . "'\n\n'");
                passthru($command);
            } else {
                exit();
            }
        }
    }

    /**
     * Build a new test request.
     *
     * @param string|null $test_class  The test class name to be found.
     * @param string|null $test_suite  The test suite.
     * @param string|null $test_module The test module.
     * @return TestRequest the new test request instance.
     */
    private function _createTestRequest($test_class = null, $test_suite = null, $test_module = null): TestRequest {
        $request = new TestRequest(
            empty($test_class) ? null : $test_class,
            empty($test_suite) ? null : $test_suite,
            empty($test_module) ? null : $test_module
        );
        $request->setMaxStringDistance(1);
        return $request;
    }

    /**
     * Builds a new lookup instance, excluding vendor directories.
     *
     * @return TestLookup New test lookup instance.
     */
    private function _createTestLookup() {
        $lookup = new TestLookup(Yii::getAlias('@app'));
        $lookup->excludeDirectories([TestFileIndex::MATCH_ANY_SUBDIR . 'vendor']);
        return $lookup;
    }

    /**
     * Run a test request allowing for interactive selection of the required tests.
     *
     * @param TestLookup  $lookup  The test lookup instance on which to run the request.
     * @param TestRequest $request The test request instance containing the requested test information.
     * @return RunCommandConstructor|false The command constructor or false on an error.
     */
    private function runInteractiveTestRequest(TestLookup $lookup, TestRequest $request) {
        $test_batch = $lookup->lookup($request);
        $error      = false;
        while (!$test_batch->canRun()) {
            if ($test_batch->isEmpty()) {
                $error = $this->_interactiveBroadenScope($request);
            } elseif ($test_batch->hasMultipleFilesToRun()) {
                $error = $this->_interactiveFileSelect($test_batch, $request);
            } elseif ($test_batch->hasConflictingModules()) {
                $error = $this->_interactiveModuleSelect($test_batch, $request);
            }
            if ($error) {
                break;
            }
            $test_batch = $lookup->lookup($request);
        }
        $module   = $test_batch->getModulesToRun()[0] ?? null;
        $suite    = $test_batch->getSuitesToRun()[0] ?? null;
        $path     = $test_batch->getFilesToRun()[0] ?? null;
        if ($error) {
            return false;
        } else {
            $constructor = new RunCommandConstructor($module, $suite, $path, null, $this->coverage, $this->silent);
            return $constructor;
        }
    }

    /**
     * Interactively selects a module.
     *
     * @param TestBatch   $current_batch The current batch holding the file options.
     * @param TestRequest $request       The request object that might be modified for the next round.
     * @return bool Whether an problem/error occurred during execution of this function.
     */
    private function _interactiveModuleSelect(TestBatch $current_batch, TestRequest &$request) {
        $suite = $current_batch->getSuitesToRun()[0] ?? '<unknown suite>';
        print_r("Suite $suite found in multiple modules.\n"
            . "Next time also specify the module.\nWhich module would you like to run now?\n");

        $pick_from   = $current_batch->getModulesToRun();
        $num_to_pick = sizeof($pick_from);

        foreach ($pick_from as $index => $module) {
            print_r("$index: $module\n");
        }

        $selected = Console::prompt('>', [
            'pattern' => '/^[0-9]+$/',
            'validator' => function($input, $error) use ($num_to_pick) {
                return (int) $input < $num_to_pick;
            }
        ]);

        if ($selected === $num_to_pick) {
            $request->setModule(null);
        } else {
            $request->setModule($pick_from[$selected]);
        }
        return false;
    }

    /**
     * Interactively broadens the scope of the tests.
     *
     * @param TestRequest $request       The request object that might be modified for the next round.
     * @return bool Whether an problem/error occurred during execution of this function.
     */
    private function _interactiveBroadenScope(TestRequest &$request) {
        print_r("No matching tests were found.\nNow what?\n");

        print_r("<enter>: Let me figure it out myself\n0: remove suite\n1: remove suite and module\n2: remove class name\n");

        $selected = Console::prompt('>', [
            'pattern' => '/^[0-9]?$/',
            'validator' => function($input, $error) {
                return $input === '' || (int) $input < 3;
            }
        ]);

        if ($selected === '') {
            print_r("Exiting...\n");
            return true;
        } elseif ($selected == 0) {
            $request->setSuite(null);
        } elseif ($selected == 1) {
            $request->setSuite(null);
            $request->setModule(null);
        } elseif ($selected == 2) {
            $request->setName(null);
        }

        return false;
    }

    /**
     * Interactively select a file from multiple options.
     *
     * @param TestBatch   $current_batch The current batch holding the file options.
     * @param TestRequest $request       The request object that might be modified for the next round.
     * @return bool Whether an problem/error occurred during execution of this function.
     */
    private function _interactiveFileSelect(TestBatch $current_batch, TestRequest &$request): bool {
        print_r("\nMultiple tests found for class '" . $request->getName() . "'.\n"
            . "Next time specify more of the path.\nWhich test file would you like to run now?\n");

        $pick_from   = $current_batch->getFilesToRun();
        $num_to_pick = sizeof($pick_from);

        foreach ($pick_from as $index => $class_path) {
            print_r("$index: $class_path\n");
        }

        $selected = Console::prompt('>', [
            'pattern' => '/^[0-9]+$/',
            'validator' => function($input, $error) use ($num_to_pick) {
                return (int) $input < $num_to_pick;
            }
        ]);

        $request->setName($pick_from[$selected]);
        return false;
    }

    /**
     * Prepares an empty test database from the current migration state.
     *
     * Creates an empty database from your current migration history.
     *
     * @param bool $force Whether to force preparing the database, even if the database is already in the current
     * migration state.
     * @return bool Whether preparing the database was successful.
     */
    public function actionPrepareDb($force = false): bool {
        try {
            $migration_namespaces = Yii::$app->controllerMap['migrate']['migrationNamespaces'] ?? null;
            $migration_controller = new MigrateController('migrate', Yii::$app, [
                'migrationNamespaces' => $migration_namespaces,
            ]);
            if (true == $force || !$this->migrationStateMatches($migration_controller)) {
                print_r("Preparing the database...\n");
                $this->prepareDb($migration_controller);
            } else {
                print_r("Test database already in correct state.\n");
            }
            return true;
        } catch (Exception $ex) {
            print_r("There was an error when preparing the test database.\n");
            $code = $ex->getCode();
            if ($code === 55006) {
                print_r("Another session is using one of the test databases, it can not be reinitialized.\n");
            } else {
                print_r($ex->getMessage());
            }
            return false;
        }
    }

    /**
     * Checks whether the current state of the test database is the same as the state of the provided migrations.
     *
     * @todo: Move to separate class.
     *
     * @param Controller $migration_controller The migration console controller.
     * @return bool Whether the migration state is up to date.
     */
    protected function migrationStateMatches(Controller $migration_controller): bool {
        $result = Yii::$app->config_db->createCommand('SELECT 1 from pg_database WHERE datname=\'' . $this->testing_template_db . '\'')->queryScalar();
        if (false == $result) {
            return false;
        }

        $dir             = opendir(Yii::getAlias($this->migration_path));
        $migration_files = [];
        while (false != ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                $parts = explode('.', $file);
                if (($parts[1] ?? '') === 'php') {
                    $migration_files[] = $parts[0];
                }
            }
        }
        sort($migration_files);

        try {
            $applied_migrations = (new Query())
                ->select(['version'])
                ->from($migration_controller->migrationTable)
                ->where('version != \'m000000_000000_base\'')
                ->column();
        } catch (Exception $ex) {
            $applied_migrations = [];
        }

        return ([] === array_diff($migration_files, $applied_migrations))
            && ([] === array_diff($applied_migrations, $migration_files));
    }

    /**
     * Internally prepares a test database.
     *
     * @todo: Move to separate class.
     *
     * The steps:
     * - Creates a new template database
     * - Runs all migrations
     * - Creates a template structure dump
     * - Creates a new empty test database from the structure dump
     *
     * @param Controller $migration_controller The migration controller.
     * @throws Exception If a database exception occurs.
     * @return void
     */
    protected function prepareDb(Controller $migration_controller) {
        Yii::$app->db->close();
        Yii::$app->config_db->createCommand('DROP DATABASE IF EXISTS ' . $this->testing_template_db)->execute();
        Yii::$app->config_db->createCommand('DROP DATABASE IF EXISTS ' . $this->testing_db)->execute();
        Yii::$app->config_db->createCommand('CREATE DATABASE ' . $this->testing_template_db)->execute();
        Yii::$app->config_db->createCommand('CREATE DATABASE ' . $this->testing_db)->execute();

        $migration_controller->runAction('up', [
            'migrationPath' => $this->migration_path,
            'interactive'   => false
        ]);

        $password    = Yii::$app->config_db->password;
        $username    = Yii::$app->config_db->username;
        $schema_file = 'schema.sql';

        $dump_command = strtr('PGPASSWORD=":password" pg_dump -d :database -h :host -p :port -U :user -s > :schema_file', [
            ':database'    => $this->testing_template_db,
            ':host'        => 'localhost',
            ':port'        => 5432,
            ':user'        => $username,
            ':password'    => $password,
            ':schema_file' => $schema_file
        ]);

        $setup_command = strtr('PGPASSWORD=":password" psql -v ON_ERROR_STOP=1 -h :host -p :port -U :user -w :database < :schema_file', [
            ':database'    => $this->testing_db,
            ':host'        => 'localhost',
            ':port'        => 5432,
            ':user'        => $username,
            ':password'    => $password,
            ':schema_file' => $schema_file
        ]);

        print_r("Creating test database...\n");
        exec($dump_command);
        exec($setup_command);
        print_r("Test database created!\n");
        @unlink($schema_file);
    }

}
