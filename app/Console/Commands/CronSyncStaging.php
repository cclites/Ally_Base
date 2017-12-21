<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class CronSyncStaging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:sync_staging';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the staging database (root only)';

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
        if (trim(`whoami`) !== 'root') {
            $this->output->error('Only root can run this command');
            exit();
        }

        if (env('APP_ENV') !== 'staging') {
            $this->output->error('This command can only be run on the staging env.');
            exit();
        }

        $stagingDb = env('DB_DATABASE');
        $productionDb = 'ally';
        $backupPathStag = '/root/backups/ally_staging_pre_sync.sql.gz';
        $backupPathProd = '/root/backups/ally_production_pre_sync.sql.gz';


        if ($stagingDb == 'ally') exit(); // extra check

        $this->output->writeln('Backing up staging database to ' . $backupPathStag);
        passthru(sprintf('mysqldump %s | gzip > %s', escapeshellarg($stagingDb), escapeshellarg($backupPathStag)), $exit);
        if ($exit) {
            $this->output->error('Error backing up staging database.');
            exit();
        }

        $this->output->writeln('Backing up production database to ' . $backupPathProd);
        passthru(sprintf('mysqldump %s | gzip > %s', escapeshellarg($productionDb), escapeshellarg($backupPathProd)), $exit);
        if ($exit) {
            $this->output->error('Error backing up staging database.');
            exit();
        }

        $this->output->writeln('Syncing production database to staging database');
        passthru(sprintf('echo DROP DATABASE %s | mysql', escapeshellarg($stagingDb)));
        passthru(sprintf('echo CREATE DATABASE %s | mysql', escapeshellarg($stagingDb)));
        passthru(sprintf('mysqldump %s | mysql %s', escapeshellarg($productionDb), escapeshellarg($stagingDb)), $exit);
        if ($exit) {
            $this->output->error('Error syncing production database to staging database.');
        }

        // Update admin password
        User::find(138)->changePassword('StagingAdmin!@');

        // Re-run migrations
        \Artisan::call('migrate');
    }
}
