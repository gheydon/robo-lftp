<?php
/**
 * @file
 */

namespace Heydon\Robo\Task;

use Heydon\Robo\Task\LFTP\Mirror;

trait loadTasks {
  /**
   * SFTP Deploy task
   */
  protected function taskLFTPMirror() {
    return $this->task(Mirror::class);
  }

}