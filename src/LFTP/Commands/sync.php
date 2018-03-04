<?php
/**
 * @files
 */

namespace Heydon\Robo\Task\LFTP\Commands;

use Heydon\Robo\Task\LFTP\CommandBase;

class sync extends CommandBase {

  private $directories = [];

  private $dryRun = FALSE;

  private $excludes = [];

  private $files = [];

  private $parallel;

  private $reverse = FALSE;

  private $targetDirectory;

  /**
   * {@inheritdoc}
   */
  public function prepare() {

    $base = [];
    $base = array_reduce($this->directories, function ($result, $a) {
      $result[dirname($a)][] = $a;
      return $result;
    }, $base);

    foreach ($base as $base => $directories) {
      $command = new mirror($this->parent, $base);

      if ($this->dryRun) {
        $command->setDryRun();
      }
      if (!empty($this->excludes)) {
        $command->setExclude(implode('|', $this->excludes));
      }
      if (isset($this->parallel)) {
        $command->setParallel($this->parallel);
      }
      if ($this->reverse) {
        $command->setReverse();
      }
      $command->setDelete();
      $command->setNoOverwrite();
      $command->setVerbose(1);

      foreach ($directories as $directory) {
        $command->setDirectory($directory);
      }

      $this->commands[] = $command;
    }

    $this->commands = array_filter(array_map(function ($a) {
      return $a->prepare();
    },$this->commands));

    return parent::prepare();
  }

  /**
   * @param mixed $directories
   */
  public function setDirectories($directories) {
    $directories = is_array($directories) ? $directories : [$directories];

    foreach ($directories as $directory) {
      $this->directories[] = $directory;
    }
  }

  /**
   *
   */
  public function setDryRun() {
    $this->dryRun = TRUE;
  }

  /**
   * @param mixed $excludes
   */
  public function setExcludes($excludes) {
    $excludes = is_array($excludes) ? $excludes : [$excludes];
    foreach ($excludes as $exclude) {
      $this->excludes[] = $exclude;
    }
  }

  /**
   * @param mixed $files
   */
  public function setFiles($files) {
    $files = is_array($files) ? $files : [$files];
    $this->files = $files;

    foreach ($files as $file) {
      $this->files[] = $file;
    }
  }

  /**
   * @param int $parallal
   */
  public function setParallel(int $parallel) {
    $this->parallel = $parallel;
  }

  /**
   *
   */
  public function setPull() {
    $this->reverse = FALSE;
  }

  /**
   *
   */
  public function setPush() {
    $this->reverse = TRUE;
  }

  /**
   * @param bool $reverse
   */
  public function setReverse(bool $reverse) {
    $this->reverse = $reverse;
  }

  /**
   * @param mixed $targetDirectory
   */
  public function setTargetDirectory($targetDirectory) {
    $this->targetDirectory = $targetDirectory;
  }

}