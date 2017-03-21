<?php

namespace branchonline\pgsqltester\cmd;

/**
 * Creates codecept run commands.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class RunCommand implements CodeceptCommand {

    /** @var string Holds the constructed command. */
    private $_command;

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
        $builder = new CodeceptCommandBuilder();
        $builder->executeAction('run');
        if (is_string($module) && $module !== '') {
            $builder->onModule($module);
        }
        if (is_string($suite) && $suite !== '') {
            $builder->onSuite($suite);
        }
        if (is_string($file) && $file !== '') {
            $builder->onFile($file);
        }
        if (is_string($function) && $function !== '') {
            $builder->onFunction($function);
        }
        if ($coverage) {
            $builder->outputHtmlCoverage();
        }
        if ($silent) {
            $builder->beSilent();
        }
        $this->_command = $builder->getCommand();
    }

    /** @inheritdoc */
    public function getCommandString(): string {
        return $this->_command;
    }
}