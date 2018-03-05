<?php
/**
 * @file
 */

namespace Heydon\Robo\Task\LFTP\Commands;

use Heydon\Robo\Task\LFTP\CommandBase;

class mirror extends CommandBase {

  private $destination;
  private $verbose;

  public function __construct(\Heydon\Robo\Task\LFTP $parent, string $destination) {
    parent::__construct($parent);

    $this->destination = $destination;
  }


  public function prepare() {
    if (isset($this->verbose)) {
      $this->option('verbose', $this->verbose, '=');
    }
    elseif (($verbosity = $this->parent->getVerbosityLevel()) && $verbosity > 1) {
      $this->option('verbose', $verbosity-1, '=');
    }
    $this->option('target-directory', $this->destination);
    return parent::prepare();
  }

  public function setDereference() {
    $this->option('dereference');
  }

  public function setDelete() {
    $this->option('delete');
  }

  public function setDirectory($directory) {
    $this->option('directory', $directory);
  }

  public function setDryRun() {
    $this->option('dry-run');
  }

  public function setExclude($rx) {
    $this->option('exclude', $rx);
  }

  public function setFile($file) {
    $this->option('file', $file);
  }

  public function setNoOverwrite() {
    $this->option('no-overwrite');
  }

  public function setParallel(int $number) {
    $this->option('parallel', $number, '=');
  }

  public function setReverse() {
    $this->option('reverse');
  }

  /**
   *
   */
  public function setSkipNoaccess() {
    $this->option('skip-noaccess');
  }

  /**
   * @param int $verbose
   */
  public function setVerbose(int $verbose) {
    $this->verbose = $verbose;
  }
}