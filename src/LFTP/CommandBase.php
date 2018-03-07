<?php
/**
 * @file
 */

namespace Heydon\Robo\Task\LFTP;

use Heydon\Robo\Task\LFTP;
use Robo\Common\CommandArguments;

abstract class CommandBase {

  use CommandArguments;

  /**
   * @var \Heydon\Robo\Task\LFTP;
   */
  protected $parent;

  /**
   * @var array
   */
  protected $commands;

  /**
   * @var string
   */
  protected $command;

  /**
   * @var string
   */
  protected $prefix = '';

  /**
   * @var string
   */
  protected $separator = '; ';

  /**
   * @var string
   */
  protected $suffix = '';

  public function __construct(LFTP $parent) {
    $this->parent = $parent;

    if (!isset($this->command)) {
      list(,,,,,$command) = explode('\\', get_called_class());
      $this->command = $command;
    }
  }

  public static function create(LFTP $parent) {
    $class = get_called_class();
    $reflection = new \ReflectionClass($class);
    $args = func_get_args();

    return $reflection->newInstanceArgs($args);
  }

  /**
   * @return string
   */
  public function getCommand() {
    return $this->command;
  }

  /**
   * @param string $command
   */
  protected function setCommand($command) {
    $this->command = $command;
  }

  /**
   * @param mixed $options
   */
  public function setOptions($options) {
    $this->options = $options;
  }

  public function __toString() {
    if (empty($this->commands)) {
      return $this->command . ' ' . $this->arguments;
    }
    else {
      return $this->prefix . implode($this->separator, $this->commands) . $this->suffix;
    }
  }

  public function prepare() {
    return $this;
  }

  /**
   * @return \Heydon\Robo\Task\LFTP
   */
  public function getParent() {
    return $this->parent;
  }

}