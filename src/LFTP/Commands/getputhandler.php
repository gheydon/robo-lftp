<?php
/**
 * @file
 */

namespace Heydon\Robo\Task\LFTP\Commands;

use Heydon\Robo\Task\LFTP;
use Heydon\Robo\Task\LFTP\CommandBase;

abstract class getputhandler extends CommandBase {

  /**
   * Source file
   *
   * @var string
   */
  protected $source;

  /**
   * The name of the file at the destination.
   *
   * @var string
   */
  protected $destinationFileName;

  public function __construct(LFTP $parent, $source) {
    parent::__construct($parent);

    $this->source = $source;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare() {
    $this->arg($this->source);
    if (isset($this->destinationFileName)) {
      $this->option('-o', $this->destinationFileName);
    }

    return parent::prepare();
  }

  /**
   * @param string $destinationFileName
   */
  public function setDestinationFileName($destinationFileName) {
    $this->destinationFileName = $destinationFileName;
  }

  /**
   * @param string $destinationDirectory
   */
  public function setDestinationDirectory($destinationDirectory) {
    $this->option('-O', $destinationDirectory);
  }
}