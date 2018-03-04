<?php
/**
 * @file
 */

namespace Heydon\Robo\Task\LFTP;

use Heydon\Robo\Task\LFTP;

trait loadTasks {

  /**
   * LFTP task
   */
  protected function taskLFTP() {
    return $this->task(LFTP::class);
  }

}