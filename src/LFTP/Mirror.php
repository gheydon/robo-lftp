<?php
/**
 * @file
 */

namespace Heydon\Robo\Task\LFTP\LFTP;


use Robo\Common\ExecCommand;
use Robo\Result;
use Robo\Task\BaseTask;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Mirror extends BaseTask {

  use ExecCommand;

  /**
   * Local directory.
   *
   * @var string
   */
  private $local;

  /**
   * remote directory.
   *
   * @var string
   */
  private $remote;

  /**
   * Remote user
   *
   * @var string
   */
  private $user;

  /**
   * Password
   *
   * @var string
   */
  private $pass;

  /**
   * Set if the mirror is going to be reversed.
   *
   * @var bool
   */
  private $reverse = FALSE;

  /**
   * Set if we want to remove destination files that don't exist in the source.
   *
   * @var bool
   */
  private $delete = FALSE;

  /**
   * Parallel processes
   *
   * @var iterable
   */
  private $parallel = 1;

  /**
   * Dry run flag.
   *
   * @var bool
   */
  private $dryRun = FALSE;

  /**
   * List of directories to mirror
   *
   * @var array
   */
  private $directories = [];

  /**
   * @var array
   */
  private $files = [];

  /**
   * Where to mirror the files to.
   *
   * @var string
   */
  private $targetDirectory;

  /**
   * list of files/directories to exclude.
   *
   * @var array
   */
  private $excludes = [];

  /**
   * @param string $local
   *
   * @return $this
   */
  public function localDirectory(string $local) {
    $fs = new Filesystem();
    if ($fs->exists($local)) {
      $this->local = realpath($local);
    }
    return $this;
  }

  public function remote(string $remote) {
    $parts = parse_url($remote);

    if (isset($parts['user'])) {
      $this->setUser($parts['user']);
      unset($parts['user']);
    }

    if (isset($parts['pass'])) {
      $this->setPassword($parts['pass']);
      unset($parts['pass']);
    }

    if (isset($parts['path'])) {
      $this->targetDirectory($parts['path']);
      unset($parts['path']);
    }

    $this->remote = http_build_url($parts);
    return $this;
  }

  public function setUser(string $user) {
    $this->user = $user;
    return $this;
  }

  public function setPassword(string $pass) {
    $this->pass = $pass;
    return $this;
  }

  public function targetDirectory($target_directory) {
    $this->targetDirectory = $target_directory;
    return $this;
  }
  /**
   * @param bool $reverse
   *
   * @return $this
   */
  public function reverse(bool $reverse) {
    $this->reverse = $reverse;
    return $this;
  }

  public function pull() {
    $this->reverse = FALSE;
    return $this;
  }

  public function push() {
    $this->reverse = TRUE;
    return $this;
  }

  public function delete($delete = TRUE) {
    $this->delete = $delete;
    return $this;
  }

  public function parallel(int $processors) {
    $this->parallel = $processors;
    return $this;
  }

  public function dryRun($dryrun = TRUE) {
    $this->dryRun = $dryrun;
    return $this;
  }

  public function directories($directories) {
    foreach ($this->toIterable($directories) as $directory) {
      $path = dirname($directory);
      $filename = basename($directory);
      $finder = Finder::create()
        ->in($this->local);
      if ($path != '.') {
        $finder->path($path);
      }
      $finder->name($filename);
      if ($finder->count()) {
        $this->directories[] = $directory;
      }
    }
    return $this;
  }

  public function files($files) {
    foreach ($this->toIterable($files) as $file) {
      $path = dirname($file);
      $filename = basename($file);
      $finder = Finder::create()
        ->in($this->local);
      if ($path != '.') {
        $finder->path($path);
      }
      $finder->name($filename);
      if ($finder->count()) {
        $this->files[] = $file;
      }
    }
    return $this;
  }

  public function excludes($excludes) {
    if (is_array($excludes)) {
      $this->excludes = array_merge($this->excludes, $excludes);
    }
    else {
      $this->excludes[] = $excludes;
    }
    return $this;
  }

  public function run() {
    $command = [];
    $lftp_commands = [];
    $mirror_args = [];

    if ($lftpexec = $this->findExecutable('lftp')) {
      $command[] = $lftpexec;
    }
    else {
      return Result::error($this, 'Unable to locate lftp command');
    }

    if ($this->local) {
//      $lftp_commands[] = 'lcd ' . $this->local;
      chdir($this->local);
    }
    else {
      return Result::error($this, 'Local directory not set.');
    }

    if ($this->targetDirectory) {
      $lftp_commands[] = 'cd ' . $this->targetDirectory;
    }
    else {
      return Result::error($this, 'Target Directory has not been set.');
    }

    if ($this->reverse) {
      $mirror_args[] = '--reverse';
    }
    if ($this->delete) {
      $mirror_args[] = '--delete';
    }
    if ($this->parallel > 1) {
      $mirror_args[] = '--parallel=' . $this->parallel;
    }
    if (!empty($this->excludes)) {
      $mirror_args[] = '-x "' . implode('|', $this->excludes) . '"';
    }
    if ($this->dryRun) {
      $mirror_args[] = '--dry-run';
    }

    $base = [];
    $base = array_reduce(array_merge($this->directories, $this->files), function ($result, $a) {
      $file = new SplFileInfo(realpath($a), dirname($a), $a);
      $result[$file->getRelativePath()][$file->getRelativePathname()] = $file;
      return $result;
    }, $base);

    foreach ($base as $base_dir => $files) {
      $lftp_command = ['mirror', implode(' ', $mirror_args)];
      $lftp_command = array_merge($lftp_command, array_map(function ($a) {
        /** @var SplFileInfo $a */
        if ($a->isDir()) {
          $option = '--directory';
        }
        else {
          $option = '--file';
        }

        return $option . ' "' . $a->getRelativePathname() . '"';
      }, $files));

      $lftp_command[] = '--target-directory "' . $base_dir . '"';

      $lftp_commands[] = implode(' ', $lftp_command);
    }

    $command[] = '-e \'' . implode('; ', $lftp_commands) . '; bye\'';

    if ($this->user) {
      $command[] = '-u ' . escapeshellarg($this->user) . (isset($this->pass) ? ',' . escapeshellarg($this->pass) : '');
    }

    $command[] = escapeshellarg($this->remote);

    $this->executeCommand(implode(' ', $command));
  }

  private function toIterable($files) {
    return is_array($files) || $files instanceof \Traversable ? $files : array($files);
  }
}