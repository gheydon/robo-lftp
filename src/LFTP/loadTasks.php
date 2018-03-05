<?php
/**
 * @file
 */

namespace Heydon\Robo\Task\LFTP;

use Heydon\Robo\Task\LFTP;
use Symfony\Component\Console\Output\OutputInterface;

trait loadTasks {

  /**
   * LFTP task
   */
  protected function taskLFTP($verbosity = NULL) {
    $verbosity = isset($verbosity) ? $verbosity : $this->getOutput()->getVerbosity();
    return $this->task(LFTP::class, $verbosity);
  }

}