<?php

namespace branchonline\pgsqltester;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
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
 */
class TestController extends Controller {

    /** @var string The name of the testing template database on which the migrations will be run. */
    public $testing_template_db = 'testing_template';

    /** @var string The name of the testing database on which the tests will be run. */
    public $testing_db = 'testing';

    /** @var string The path to the migrations folder. */
    public $migration_path = '@console/migrations/';

    /** @var string The codeception suite to run the tests from. */
    public $suite = '';

    /** @var string The system module to run the tests for. */
    public $for_module = '';

    /** @var string The type of coverage to output, by default outputs no coverage. */
    public $coverage = false;

    /** @inheritdoc */
    public function options($actionID) {
        return $actionID === 'run' ? ['suite', 'for_module', 'coverage'] : [];
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
        passthru('composer exec codecept build');
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
        if ($this->for_module !== '' && $this->suite === '') {
            $this->suite = 'unit';
        }
        if ($this->actionPrepareDb()) {
            $command = $this->prepareCodeceptCommand($test_class, $test_function);
            if (is_string($command)) {
                print_r('About to run \'' . $command . "'\n\n'");
                passthru($command);
            } else {
                exit();
            }
        }
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
            $migration_controller = new MigrateController('migrate', Yii::$app);
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
     * Prepare a codeception argument string to run specific tests only.
     *
     * @param string $module            The system module to run the tests for.
     * @param string $test_class        The name of the test class that you want to run.
     * @param string $raw_test_function The name of the test function that you want to run.
     * @return string|false The argument string to be passed to the codecept executor or false if an error occurred.
     */
    protected function prepareCodeceptCommand(string $test_class, string $raw_test_function) {
        $error         = false;
        $test_function = '';
        $test_path     = '';
        if ('' !== $test_class) {
            $class_paths = $this->lookupTestClass($test_class);
            if (is_array($class_paths)) {
                $classes_to_pick = sizeof($class_paths);
                if ($classes_to_pick === 1) {
                    $test_path = $class_paths[0];
                } else {
                    print_r("\nMultiple tests found for given test class '$test_class'.\n"
                        . "Next time use the fully qualified class path.\nWhich one would you like to run now?\n");
                    foreach ($class_paths as $index => $class_path) {
                        print_r("$index: $class_path\n");
                    }
                    $selected = Console::prompt('>', [
                        'pattern' => '/^[0-9]+$/',
                        'validator' => function($input, $error) use ($classes_to_pick) {
                            return (int) $input < $classes_to_pick;
                        }
                    ]);
                    if ('' === $selected) {
                        print_r("Nothing selected, exiting...\n");
                        $error = true;
                    } else {
                        $test_path = $class_paths[$selected];
                    }
                }
                if (''!== $test_path && '' !== $raw_test_function) {
                    $test_function = (1 === preg_match('/^test/', $raw_test_function)) ? $raw_test_function : 'test' . ucfirst($raw_test_function);
                }
            } else {
                print_r("No test found for given test class '$test_class'\nPerhaps a spelling error? Or maybe you forgot to mention the suite?\n");
                $error = true;
            }
        }
        return $error ? false : $this->formatTestCommand($this->for_module, $this->suite, $test_path, $test_function);
    }

    /**
     * Formats a complete command from the given parts.
     *
     * @param string $module        The module for which to run the tests.
     * @param string $suite         The suite from which to run the tests.
     * @param string $test_path     The full path to the test class to be run.
     * @param string $test_function The test function from the class to be run.
     * @return string The complete test command.
     */
    protected function formatTestCommand($module, $suite, $test_path, $test_function) {
        $command = ['composer exec codecept run'];
        if ($suite !== '') {
            $command[] = $suite;
        }
        $coverage = $this->coverage === false ? '' : '--coverage-html';
        if ($module !== '' || $test_path !== '' || $test_function !== '' || $coverage !== '') {
            $command[] = '--';
            if ($module !== '') {
                $command[] = "-c $module";
            }
            if ($test_path !== '') {
                if ($test_function !== '') {
                    $command[] = "$test_path::$test_function";
                } else {
                    $command[] = $test_path;
                }
            }
            if ($coverage !== '') {
                $command[] = $coverage;
            }
        }
        return implode(' ', $command);
    }

    /**
     * Lookup whether there is a test that is specified by the given class name.
     *
     * @param string $class_name The name of the class, accepts simpel names,
     * with or without Test/extension or fully qualified paths.
     * @return false|array False if no test can be found to match, or an array
     * if there exist matching tests.
     */
    protected function lookupTestClass(string $class_name) {
        if ($class_name === '') {
            return false;
        }
        $class_name = preg_replace('/^\//', '', $class_name);
        $parts      = explode(DIRECTORY_SEPARATOR, $class_name);
        if (sizeof($parts) === 0) {
            return false;
        } if (sizeof($parts) > 1) {
            return [$class_name];
        }
        $file_name = array_pop($parts);
        $file_name = preg_replace('/test(.php)?$/', '', strtolower($file_name));
        $index     =  $this->getTestIndexForModule();
        $options   = $index[$this->suite][$file_name] ?? [];
        if (sizeof($options) === 0) {
            return false;
        } else {
            return $options;
        }
    }

    /**
     * Builds a test index used for looking up the tests on a given class.
     *
     * @return array The test index.
     */
    protected function getTestIndexForModule() {
        $dirs          = ['tests'];
        if ($this->for_module !== '') {
            array_unshift($dirs, $this->for_module);
        }
        try {
            $test_base_dir = Yii::getAlias('@' . implode(DIRECTORY_SEPARATOR, $dirs) . DIRECTORY_SEPARATOR);
        } catch (InvalidParamException $ex) {
            $test_base_dir = Yii::getAlias('@app/' . implode(DIRECTORY_SEPARATOR, $dirs) . DIRECTORY_SEPARATOR);
        }
        $test_index    = [];
        $it            = new RecursiveDirectoryIterator($test_base_dir);
        foreach (new RecursiveIteratorIterator($it) as $file) {
            if (1 === preg_match('/Test.php$/', $file->getBaseName())) {
                $test_name     = strtolower(preg_replace('/Test.php/', '', $file->getBaseName()));
                $relative_path = str_replace($test_base_dir, '', $file->getPathName());
                $paths_part    = explode(DIRECTORY_SEPARATOR, $relative_path);
                $suite         = array_shift($paths_part);
                if (!isset($test_index[$suite][$test_name])) {
                    $test_index[$suite][$test_name] = [implode(DIRECTORY_SEPARATOR, $paths_part)];
                } elseif (is_array($test_index[$suite][$test_name])) {
                    $test_index[$suite][$test_name][] = implode(DIRECTORY_SEPARATOR, $paths_part);
                }
            }
        }
        return $test_index;
    }

    /**
     * Checks whether the current state of the test database is the same as the state of the provided migrations.
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
