<?php
/**
 * @file
 */

namespace Heydon\Robo\Task\LFTP\Commands;


use Heydon\Robo\Task\LFTP\CommandBase;
use Heydon\Robo\Task\LFTP;
use Robo\Common\CommandArguments;

class cd extends CommandBase {

  use CommandArguments;

  protected $destination;

  public function __construct(LFTP $parent, $destination) {
    parent::__construct($parent);

    $this->setDestination($destination);
  }

  public function setDestination($destination) {
    $this->destination = $destination;
    $this->arg($destination);
  }
}