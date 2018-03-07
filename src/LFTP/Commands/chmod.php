<?php
/**
 * @file
 */

namespace Heydon\Robo\Task\LFTP\Commands;

use Heydon\Robo\Task\LFTP;
use Heydon\Robo\Task\LFTP\CommandBase;

class chmod extends CommandBase {

  /**
   * @var bool $recursive
   */
  private $recursive = FALSE;

  /**
   * @var string $mode.
   */
  private $mode;

  /**
   * @var string $filename
   */
  private $filename;

  /**
   * chmod constructor.
   *
   * @param LFTP $parent
   * @param string $mode
   * @param string $filename
   */
  public function __construct(LFTP $parent, $mode, $filename) {
    parent::__construct($parent);

    $this->mode = $mode;
    $this->filename = $filename;
  }

  public function prepare() {
    if ($this->recursive) {
      $this->option('recursive');
    }
    $this->args([$this->mode, $this->filename]);

    return parent::prepare();
  }

}