<?php

namespace branchonline\pgsqltester\filesystem;

use RecursiveFilterIterator;

class TestDirFilter extends RecursiveFilterIterator {

    protected $exclude;

    public function __construct($iterator, array $exclude) {
        parent::__construct($iterator);
        $this->exclude = $exclude;
    }

    public function accept() {
        if ($this->isDir()) {
            $is_excluded = in_array($this->getFilename(), $this->exclude);
            $is_hidden = substr($this->getFilename(), 0, 1) === '.';

            return !($is_excluded || $is_hidden);
        } else {
            return boolval(preg_match('/Test.php$/', $this->getBaseName()));
        }
    }

    public function getChildren() {
        return new TestDirFilter($this->getInnerIterator()->getChildren(), $this->exclude);
    }

}
