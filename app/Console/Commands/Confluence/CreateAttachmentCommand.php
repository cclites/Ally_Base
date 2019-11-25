<?php

namespace App\Console\Commands\Confluence;

use App\Services\ConfluenceApiClient;
use Illuminate\Console\Command;

class CreateAttachmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'confluence:create-attachment {content_id} {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload an attachment to the given Confluence content page.';

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
        $contentId = $this->argument('content_id');
        $filename = $this->argument('filename');
        $name = basename($filename);

        if (! file_exists($filename)) {
            $this->error("File does not exist: $filename");
            return 1;
        }

        $this->info("Uploading $name to Confluence page $contentId...");

        if ($this->client->uploadAttachment($contentId, $filename, basename($filename))) {
            $this->info('Attachment successfully uploaded.');
        }
        else {
            $this->error('Attachment upload failed.');
        }

        $this->info("Operation complete.");
        return 0;
    }
}
