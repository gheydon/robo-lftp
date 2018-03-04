<?php
/**
 * @file
 */

namespace Heydon\Robo\Task;

use Robo\Common\ExecOneCommand;
use Robo\Common\TaskIO;
use Robo\Result;
use Robo\Task\BaseTask;
use Symfony\Component\Finder\SplFileInfo;

class LFTP extends BaseTask {

  use TaskIO;
  use ExecOneCommand;

  /**
   * @var array
   */
  private $commands = [];

  private $currentCommand;

  /**
   * @var SplFileInfo $cwd.
   */
  private $cwd;

  protected $prefix = '';
  protected $separator = "\n";
  protected $suffix = "\n";

  public function __construct() {

    $this->cwd = new SplFileInfo(getcwd(), '..', '.');

    $this->set('cmd:fail-exit', 1);
  }

  public function run() {
    if (empty($this->commands)) {
      return Result::error($this, 'No commands, nothing to do.');
    }
    if ($lftpexec = $this->findExecutable('lftp')) {
      $exec = $lftpexec;
    }
    else {
      return Result::error($this, 'Unable to locate lftp command');
    }

    if ($filename = $this->buildCommands()) {
      $this->option('-f', $filename);
    }

    $exec .= ' ' . $this->arguments;

    return $this->executeCommand($exec);
  }

  public function __call($fn, $arguments) {
    $class = "Heydon\\Robo\\Task\\LFTP\\Commands\\{$fn}";
    if (class_exists($class)) {
      array_unshift($arguments, $this);
      $this->currentCommand = call_user_func_array([$class, 'create'],$arguments);
      $this->commands[] = $this->currentCommand;
      return;
    }

    if (!isset($this->currentCommand)) {
      throw new \BadMethodCallException("No such method $fn: current command is undefined.");
    }

    return call_user_func_array([$this->currentCommand, $fn], $arguments);
  }

  public function buildCommands(): string {
    $file = tempnam(sys_get_temp_dir(), 'lftp');

    $commands = array_filter(array_map(function ($a) {
      return $a->prepare();
    },$this->commands));

    $script = $this->prefix . implode($this->separator, $commands) . $this->suffix;

    $this->printTaskInfo($script);

    file_put_contents($file, $script);

    return $file;
  }

  /**
   * @return SplFileInfo
   */
  public function getCwd(): SplFileInfo {
    return $this->cwd;
  }

  /**
   * @param SplFileInfo $cwd
   */
  public function setCwd(SplFileInfo $cwd) {
    $this->cwd = $cwd;
  }
}