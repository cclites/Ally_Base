<?php

namespace App\Console\Commands\Cron;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CronCleanUpAchFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:clean-ach-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up stale ACH export files from local and SFTP disks.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Cleaning local ACH export files...");

        collect(\Storage::disk('local')->files('ach' . DIRECTORY_SEPARATOR . 'exports'))
            ->each(function ($filename) {
                if (! Str::endsWith($filename, '.xlsx')) {
                    return;
                }

                $time = \Storage::disk('local')->lastModified($filename);
                $cutoff = Carbon::now()->subHours(48);

                if (Carbon::createFromTimestamp($time)->isBefore($cutoff)) {
                    $this->info("Deleting $filename...");
                    \Storage::disk('local')->delete($filename);
                }
            });

        $this->info("Cleaning remote SFTP ACH export files...");

        collect(\Storage::disk('sftp-ach')->files())
            ->each(function ($filename) {
                if (! Str::endsWith($filename, '.xlsx')) {
                    return;
                }

                $time = \Storage::disk('sftp-ach')->lastModified($filename);
                $cutoff = Carbon::now()->subHours(48);

                if (Carbon::createFromTimestamp($time)->isBefore($cutoff)) {
                    $this->info("Deleting $filename...");
                    \Storage::disk('sftp-ach')->delete($filename);
                }
            });

        $this->info("Operation complete.");

        return 0;
    }
}
