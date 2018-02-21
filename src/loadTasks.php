<?php
/**
 * @file
 */

namespace Heydon\Robo\Task\LFTP;

use Heydon\Robo\Task\LFTP\LFTP\Mirror;

trait loadTasks {
  /**
   * SFTP Deploy task
   */
  protected function taskLFTPMirror() {
    return $this->task(Mirror::class);
  }

}