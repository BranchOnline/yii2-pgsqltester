Yii2 PostgreSQL automated testing setup
=======================================

Automated setup of empty database and powerful console control of available tests.

#### Example usage
First set up the environment using the instructions in example setup below.

Run all tests:

 ```./yii_test test/run``` or ```./yii_test test``` (action run is default)

Run all unit tests using (only when you are not using modules, otherwise you have to specify the module):

```./yii_test test -s=unit```

Run all unit tests in module admin using:

```./yii_test test -m=admin```

Run all unit tests in module admin from suite acceptance:

```./yii_test test -s=acceptance -m=admin```

Run specific test by name:

```./yii_test test TestName```

Force to remigrate the test database:

```./yii_test test/prepare-db true```

#### Example setup

**Read carefully and make sure you understand what you're doing before setting anything up!**

###### Databases
This package provides a clean separation of databases so your tests can always
run on an empty database that is in the correct state. To see how it works we identify 4 databases:
- The *dev* database. This database is not touched by the testing mechanism, but should remain accessible for developing.
- The *template* database. This is the database that is migrated to the correct version by the testing system, and may contain all kinds of data that your migrations add by default.
- The *test* databse. This is the database that the actual tests run on. It is prepared by the testing mechanism to be a completely empty copy of the template database.
- The *postgres* databse. This is the default database provided by postgres, that is used by the tester to automatically setup both *template* and *test* database.

###### Yii setup
You need 2 config files for this setup to work:
- *test.php* should contain credentials for the *postgres* and *test* database.
- *test-setup.php* should overwrite the credentials of *test* with that of *template* and make the test controller available through the controller map.

An example for *test.php*:
```
<?php
return [
    'id' => 'basic-tests',
    'basePath' => dirname(__DIR__),
    'language' => 'en-US',
    'components' => [
        'config_db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=localhost;port=5432;dbname=postgres',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=localhost;port=5432;dbname=testing',
        ],
    ],
];
```

An example for *test-setup.php*"
```
<?php
return [
    'components' => [
        'db' => [
            'dsn' => 'pgsql:host=localhost;port=5432;dbname=testing_template'
        ],
    ],
    'controllerMap' => [
        'test' => [
            'class' => 'branchonline\pgsqltester\TestController',
        ]
    ],
];
```

Make sure you have a access point for running the tests, for instance ./yii_test.

yii_test example (adjust to your liking):
```
<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/path/to/test.php'),
    require(__DIR__ . '/path/to/test-setup.php')
);

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
```
Make sure the YII_ENV is initialized to 'test'.
Also make sure you include *test-setup.php* and *test.php* in that order.

###### Codeception setup
Codeception is run using the codeception.yml file as configuration,
make sure to configure the Yii module and include *test.php* as the access point.
```
modules:
    config:
        Yii2:
            configFile: 'path/to/test.php'
```