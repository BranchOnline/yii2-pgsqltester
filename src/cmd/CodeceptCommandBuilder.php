<?php

namespace branchonline\pgsqltester\cmd;

/**
 * Construct composer codecept commands through an object interface allowing for easy manipulation.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class CodeceptCommandBuilder {

    /** @var string The action to be executed on the codecept program. */
    private $_action;

    /** @var bool Whether to run composer in verbose mode. */
    private $_verbose = true;

    /** @var string|false The coverage, if false use no coverage, else use the coverage type as given by this var. */
    private $_coverage = false;

    /** @var string|false The module on which to execute the command or false if no module should be used. */
    private $_module = false;

    /** @var string|false The suite on which to execute the command or false if no suite should be used. */
    private $_suite = false;

    /** @var string|false The path to the file on which to execute the command or false if no file should be used. */
    private $_file = false;

    /** @var string|false The name of the function to append to the command, or false if no function should be used. */
    private $_function = false;

    /** @param string $action The name of the codecept action to be executed. */
    public function executeAction(string $action) {
        $this->_action = $action;
    }

    /** Disable verbose composer output. */
    public function beSilent() {
        $this->_verbose = false;
    }

    /** @param string $suite Specify the suite to be put into the command. */
    public function onSuite(string $suite) {
        $this->_suite = $suite;
    }

    /** @param string $module Specify the module to be put into the command. */
    public function onModule(string $module) {
        $this->_module = $module;
    }

    /** @param string $file Specify the file to be put into the command. */
    public function onFile(string $file) {
        $this->_file = $file;
    }

    /** @param string $function Specify the function to be put into the command. */
    public function onFunction(string $function) {
        $this->_function = $function;
    }

    /** Output html coverage */
    public function outputHtmlCoverage() {
        $this->_coverage = 'html';
    }

    /**
     * Get the final command constructed by the builder.
     *
     * @return string The constructed command.
     */
    public function getCommand() {
        $command_parts   = $this->_getCommandParts();
        $optstring_parts = $this->_getOptstringParts();

        if (empty($optstring_parts)) {
            $final_parts = $command_parts;
        } else {
            $final_parts = array_merge($command_parts, ['--'], $optstring_parts);
        }

        return $this->_formatCommand($final_parts);
    }

    /** @return array A list with the optstring parts (part after the --) */
    private function _getOptstringParts() {
        $command_parts = [];
        if (false !== $this->_module) {
            $command_parts[] = '-c ' . $this->_module;
        }
        if (false !== $this->_file) {
            if (false !== $this->_function) {
                $command_parts[] = $this->_file . '::' . $this->_function;
            } else {
                $command_parts[] = $this->_file;
            }
        }
        if (false !== ($coverage_type = $this->_coverage)) {
            $command_parts[] = "--coverage-$coverage_type";
        }
        return $command_parts;
    }

    /** @return array A list with all the command parts (part before the --) */
    private function _getCommandParts(): array {
        $command_parts = ['composer', 'exec', 'codecept', $this->_action];
        if ($this->_verbose) {
            $command_parts[] = '-v';
        }
        if (false !== $this->_suite) {
            $command_parts[] = $this->_suite;
        }
        return $command_parts;
    }

    /**
     * Turns command parts into a command string.
     *
     * @param array $parts The command parts.
     * @return string The complete command.
     */
    private function _formatCommand(array $parts): string {
        return implode(' ', $parts);
    }

}