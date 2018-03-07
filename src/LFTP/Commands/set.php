<?php
/**
 * @file
 */

namespace Heydon\Robo\Task\LFTP\Commands;

use Heydon\Robo\Task\LFTP\CommandBase;
use Heydon\Robo\Task\LFTP;

class set extends CommandBase {

  public function __construct(LFTP $parent, $variable, $value) {
    $this->args([$variable, $value]);
    parent::__construct($parent);
  }
}