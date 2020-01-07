<?php

namespace App\Traits\Console;

trait HasLog
{
    /**
     * The command log.
     * @var array
     */
    protected $_log = [];

    /**
     * Log a message and optionally output it to the console.
     *
     * @param string $message
     * @param string $outputMethod
     */
    public function log(string $message, ?string $outputMethod = 'info'): void
    {
        if ($outputMethod) {
            $this->$outputMethod($message);
        }

        $this->_log[] = $message;
    }

    /**
     * Get the log array.
     *
     * @return array
     */
    public function getLog(): array
    {
        return $this->_log;
    }

    /**
     * Clear the current log.
     */
    protected function flushLog() : void
    {
        $this->_log = [];
    }
}