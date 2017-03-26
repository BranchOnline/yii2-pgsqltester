<?php

namespace branchonline\pgsqltester\cmd;

/**
 * Creates codecept run commands.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class RunCommandConstructor implements CodeceptCommandConstructor {

    /** @var CodeceptCommandBuilder Holds the constructed command. */
    private $_builder;

    /**
     * Construct a new codecept build command.
     *
     * @param string|null  $module   The module on which to run the tests. Leave null to use all modules.
     * @param string|null  $suite    The suite on which to run the tests. Leave null to use all suites.
     * @param string|null  $file     The test file to run. Leave null to run all files in selected suite/modules.
     * @param string|null  $function The test function to run. Leave null to run all functions in selected file(s).
     * @param bool         $coverage Whether to output html coverage.
     * @param bool         $silent   Whether to build silently.
     */
    public function __construct($module = null, $suite = null, $file = null, $function = null, $coverage = false, bool $silent = false) {
        $this->_builder = new CodeceptCommandBuilder();
        $this->_builder->executeAction('run');
        if (is_string($module) && $module !== '') {
            $this->_builder->onModule($module);
        }
        if (is_string($suite) && $suite !== '') {
            $this->_builder->onSuite($suite);
        }
        if (is_string($file) && $file !== '') {
            $this->_builder->onFile($file);
        }
        if (is_string($function) && $function !== '') {
            $this->_builder->onFunction($function);
        }
        if ($coverage) {
            $this->_builder->outputHtmlCoverage();
        }
        if ($silent) {
            $this->_builder->beSilent();
        }
    }

    /**
     * Specify the test function to be executed.
     *
     * @param string $function The name of the test function to be executed.
     *
     * @return void
     */
    public function setFunction(string $function) {
        $this->_builder->onFunction($function);
    }

    /** @inheritdoc */
    public function getCommand(): string {
        return $this->_builder->getCommand();
    }
}