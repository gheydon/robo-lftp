<?php
/**
 * @files
 */

namespace Heydon\Robo\Task\LFTP\Commands;

use Heydon\Robo\Task\LFTP\CommandBase;

class sync extends CommandBase {

  private $dereference = FALSE;

  private $directories = [];

  private $dryRun = FALSE;

  private $excludes = [];

  private $files = [];

  private $parallel;

  private $reverse = FALSE;

  protected $separator = ' && ';

  private $skipNoaccess = FALSE;

  private $targetDirectory;

  /**
   * {@inheritdoc}
   */
  public function prepare() {

    $base = [];
    foreach (['directories', 'files'] as $type) {
      $base = array_reduce($this->$type, function ($result, $a) use ($type) {
        $result[dirname($a)][$type][] = $a;
        return $result;
      }, $base);
    }

    foreach ($base as $base => $items) {
      $command = new mirror($this->parent, $base);

      if ($this->dereference) {
        $command->setDereference();
      }
      if ($this->dryRun) {
        $command->setDryRun();
      }
      if (!empty($this->excludes) && ($excludes = $this->buildExcludes($base))) {
        $command->setExclude($excludes);
      }
      if (isset($this->parallel)) {
        $command->setParallel($this->parallel);
      }
      if ($this->reverse) {
        $command->setReverse();
      }
      if ($this->skipNoaccess) {
        $command->setSkipNoAccess();
      }

      $command->setDelete();
      $command->setNoOverwrite();

      if (!empty($items['directories'])) {
        foreach ($items['directories'] as $directory) {
          $command->setDirectory($directory);
        }
      }
      if (!empty($items['files'])) {
        foreach ($items['files'] as $file) {
          $command->setFile($file);
        }
      }

      $this->commands[] = $command;
    }

    $this->commands = array_filter(array_map(function ($a) {
      return $a->prepare();
    },$this->commands));

    return parent::prepare();
  }

  /**
   * When building the mirror command the excludes need to be relative to the
   * base directory. So this method converts all the exclude to a relative
   * path, and filters out anything that is not in the base path.
   *
   * @param string $base
   *
   * @return string
   */
  private function buildExcludes(string $base) {
    if ($base == '.') {
      return implode('|', $this->excludes);
    }

    $excludes = [];
    foreach ($this->excludes as $exclude) {
      // Check and see if this is an exclude which applies to all directories
      // like '.git'
      if (strpos($exclude, '/') === FALSE) {
        $excludes[] = $exclude;
      }
      // this must be a path so check it it occurs within the current base.
      else {
        // If the exclude starts with ./ then string that off before checking if
        // it is in the base directory.
        if (substr($exclude, 0, 2) == './') {
          $exclude = substr($exclude, 2);
        }
        if (substr($exclude, 0, strlen($base)) == $base) {
          $excludes[] = substr($exclude, strlen($base)+1);
        }
      }
    }

    return implode('|', $excludes);
  }

  /**
   *
   */
  public function setDereference() {
    $this->dereference = TRUE;
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
   *
   */
  public function setSkipNoaccess() {
    $this->skipNoaccess = TRUE;
  }

  /**
   * @param mixed $targetDirectory
   */
  public function setTargetDirectory($targetDirectory) {
    $this->targetDirectory = $targetDirectory;
  }

}