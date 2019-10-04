<?php

namespace App\Traits\Console;

use Symfony\Component\Console\Helper\ProgressBar;

trait HasProgressBars
{
    /**
     * @var ProgressBar
     */
    protected $progressBar;

    /**
     * @var int
     */
    protected $progressTotal;

    /**
     * Helper to create a progress bar.
     *
     * @param string $status
     * @param int $total
     */
    protected function startProgress(string $status, int $total) : void
    {
        $this->info($status);
        $this->progressTotal = $total;
        $this->progressBar = $this->output->createProgressBar($this->progressTotal);
    }

    /**
     * Advance the current progress bar.
     *
     * @param int $size
     */
    protected function advance(int $size = 1) : void
    {
        $this->progressBar->advance($size);
    }

    /**
     * Complete the current progress bar.
     */
    protected function finish() : void
    {
        $this->progressBar->setProgress($this->progressTotal);
        $this->line('');
    }
}