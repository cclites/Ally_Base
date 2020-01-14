<?php

namespace App\Console\Commands;

use App\Services\ConfluenceApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class RestoreDataDumpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore-dump {--content_id= : The confluence content ID where the database dumps are stored.} {--zip_password= : The password of the encrypted zip file containing the dump.} {--delete : Delete the downloaded files after restore.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download and restore data dump from confluence server.';

    /**
     * @var ConfluenceApiClient
     */
    protected $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->client = app(ConfluenceApiClient::class);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $contentId = $this->option('content_id') ?? config('ally.data_dump.confluence_content_id');
        if (empty($contentId)) {
            $this->error('No confluence content ID was set for the data dump list.');
            return  1;
        }

        $zipPassword = $this->option('zip_password') ?? config('ally.data_dump.zip_password');

        $this->info('Fetching latest database dumps from Confluence...');
        $attachments = collect($this->client->getContentAttachments($contentId));

        if ($attachments->isEmpty()) {
            $this->error("Error getting attachment list from Confluence document $contentId");
            return 1;
        }

        $choices = $attachments->pluck(['filename'])->toArray();
        $choice = $this->choice('Which dump would you like to restore?', $choices, count($choices)-1);

        $attachment = $attachments->where('filename', '=', $choice)->first();
        $zipFile = $attachment['filename'];

        $this->info("Downloading $zipFile...");
        if (! $this->client->download($attachment['url'], $zipFile)) {
            $this->error("Error downloading file: $zipFile");
            return 1;
        }

        $this->info("Unzipping $zipFile...");
        $process = new Process(['unzip', '-o', '-P', $zipPassword, $zipFile]);
        $process->setTimeout(1200); // this can take a while
        $process->run();
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $sqlFile = substr(basename($zipFile), 0, Str::length(basename($zipFile))-4);

        $connection = config('database.default');
        $host = config("database.connections.$connection.host");
        $port = config("database.connections.$connection.port");
        $databaseName = config("database.connections.$connection.database");
        $databaseUser = config("database.connections.$connection.username");
        $databasePassword = config("database.connections.$connection.password");

        $this->info("Dropping existing database...");
        $process = new Process("mysql -u $databaseUser --password=$databasePassword -h $host -P $port -Nse  'drop database if exists $databaseName; create database $databaseName'");
        $process->setTimeout(1200); // this can take a while
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->info("Restoring backup sql file...");
        $process = new Process("mysql -u $databaseUser --password=$databasePassword -h $host -P $port $databaseName < $sqlFile");
        $process->setTimeout(1200); // this can take a while
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if ($this->option('delete')) {
            $this->info('Cleaning up files...');
            unlink($sqlFile);
            unlink($zipFile);
        }
        
        $this->info("Database dump $sqlFile as been successfully restored.  Make sure to run the following artisan command to set default passwords and repair encryption issues.\nphp artisan clear:sensitive_data demo --fix-only --fast");
        return 0;
    }
}
