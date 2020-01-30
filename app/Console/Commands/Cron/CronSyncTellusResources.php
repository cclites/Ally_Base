<?php

namespace App\Console\Commands\Cron;

use App\Services\AnonymousConfluenceApiClient;
use App\Console\Commands\BaseImport;
use App\Services\TellusService;
use App\TellusEnumeration;
use App\TellusTypecode;

class CronSyncTellusResources extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:sync-tellus-resources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the Tellus Typecode Data Dictionary and XML Schema XSD files and import the data dictionary to the local database.';

    /** @var AnonymousConfluenceApiClient */
    protected $confluenceClient;

    /** @var string */
    protected $typecodeContentId;

    /** @var string */
    protected $renderedServicesContentId;

    /** @var string */
    protected $xsdSearchString;

    /** @var string */
    protected $dictionaryFile;

    /** @var string */
    protected $schemaFile;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->confluenceClient = new AnonymousConfluenceApiClient(config('services.tellus.confluence_host'));
        $this->typecodeContentId = config('services.tellus.typecode_content_id');
        $this->renderedServicesContentId = config('services.tellus.rendered_services_content_id');
        $this->xsdSearchString = config('services.tellus.xsd_search_string');
    }

    /**
     * Down
     *
     * @return mixed|void
     */
    public function handle()
    {
        $this->dictionaryFile = $this->downloadTypecodeDictionary();
        $this->schemaFile = $this->downloadXmlSchema();

        $this->info("Importing new list of Tellus Enumerations...");
        \DB::table('tellus_enumerations')->truncate();
        $enumerations = $this->importEnumerationsFromSchema($this->schemaFile);
        $this->info("Imported {$enumerations} Tellus Enumerations.");

        // Handle importing typecodes from BaseImporter
        \DB::table('tellus_typecodes')->truncate();
        parent::handle();

        $this->info("Operation complete.");
    }

    /**
     * Download the Typecode Data Dictionary excel file
     * and return the path on the server.
     *
     * @return string|null
     */
    public function downloadTypecodeDictionary(): ?string
    {
        $attachments = collect($this->confluenceClient->getContentAttachments($this->typecodeContentId));

        if ($attachments->isEmpty()) {
            $this->error("Error locating Tellus Typecode Dictionary file on Confluence (Content ID: {$this->typecodeContentId})");
            return null;
        }

        $url = $attachments[0]['url'];
        $filename = $attachments[0]['filename'];

        \Storage::disk('local')->makeDirectory('tellus');
        $localFile = \Storage::disk('local')->path("tellus/$filename");

        $this->info("Downloading $filename...");
        if (!$this->confluenceClient->download($url, $localFile)) {
            $this->error("Error downloading file: $filename");
            return null;
        }

        return $localFile;
    }

    /**
     * Download the XML Schema validation file
     * and return the path on the server.
     *
     * @return string|null
     */
    public function downloadXmlSchema(): ?string
    {
        $attachments = collect($this->confluenceClient->getContentAttachments($this->renderedServicesContentId));
        if ($attachments->isEmpty()) {
            $this->error("Error fetching file listing for Tellus Rendered Services File Specifications. (Content ID: {$this->renderedServicesContentId})");
            return null;
        }

        $xsdAttachments = $attachments->filter(function ($attachment) {
            return strpos($attachment['filename'], $this->xsdSearchString) > 0;
        });

        if (empty($xsdAttachments) || $xsdAttachments->count() != 1) {
            $this->error("Error locating the Tellus XML Schema XSD file.  (Search string: {$this->xsdSearchString})");
            return null;
        }

        $attachment = $xsdAttachments->first();

        $url = $attachment['url'];
        $filename = $attachment['filename'];
        $localFile = \Storage::disk('public')->path(TellusService::XML_SCHEMA_FILENAME);

        $this->info("Downloading $filename...");
        if (!$this->confluenceClient->download($url, $localFile)) {
            $this->error("Error downloading file: $filename");
            return null;
        }

        return $localFile;
    }

    /**
     * @deprecated
     * At the time of of this code 9/26/2019 the tellus typecode dictionary
     * has errors and mismatches from the XSD schema.  This will handle
     * fixing those errors automatically.
     *
     */
    public function cleanKnownDictionaryIssues()
    {
        // tellus introduced these new errors of course
        TellusTypecode::where('category', 'EndVerificationType')
            ->where('text_code', 'GPS')
            ->update(['description' => 'GPS Verification Method']);
        TellusTypecode::where('category', 'EndVerificationType')
            ->where('text_code', 'IVR')
            ->update(['description' => 'IVR Verification Method']);
        TellusTypecode::where('category', 'EndVerificationType')
            ->where('text_code', 'QCD')
            ->update(['description' => 'Q-Code Verification Method']);
        TellusTypecode::where('category', 'EndVerificationType')
            ->where('text_code', 'TOK')
            ->update(['description' => 'Digital Token Verification Method']);

        TellusTypecode::where('category', 'StartVerificationType')
            ->where('text_code', 'GPS')
            ->update(['description' => 'GPS Verification Method']);
        TellusTypecode::where('category', 'StartVerificationType')
            ->where('text_code', 'IVR')
            ->update(['description' => 'IVR Verification Method']);
        TellusTypecode::where('category', 'StartVerificationType')
            ->where('text_code', 'QCD')
            ->update(['description' => 'Q-Code Verification Method']);
        TellusTypecode::where('category', 'StartVerificationType')
            ->where('text_code', 'TOK')
            ->update(['description' => 'Digital Token Verification Method']);
    }

    /**
     * Handle parsing of XML Schema file, returns number of
     * enumerations added.
     *
     * @param string $filename
     * @return int
     */
    public function importEnumerationsFromSchema(string $filename) : int
    {
        $xsd = file_get_contents($filename);
        $doc = new \DOMDocument();
        $doc->loadXML(mb_convert_encoding($xsd, 'utf-8', mb_detect_encoding($xsd)));
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('xs', 'http://www.w3.org/2001/XMLSchema');

        $elements = $xpath->evaluate("/xs:schema/xs:element");
        $count = 0;
        foreach ($elements as $el) {
            $category = $el->getAttribute('name');
            $enums = $xpath->evaluate("xs:complexType/xs:simpleContent/xs:extension/xs:attribute/xs:simpleType/xs:restriction/xs:enumeration", $el);

            if (count($enums) == 0) {
                continue;
            }

            for ($i = 0; $i < count($enums); $i++) {
                TellusEnumeration::create([
                    'category' => $category,
                    'code' => $enums[$i]->getAttribute('value'),
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Load the import spreadsheet into $sheet
     *
     * @return \PHPExcel
     * @throws \PHPExcel_Reader_Exception
     */
    public function loadSheet()
    {
        if (!$objPHPExcel = \PHPExcel_IOFactory::load($this->dictionaryFile)) {
            $this->output->error('Could not load dictionary file at ' . $this->dictionaryFile);
            exit;
        }
        return $this->sheet = $objPHPExcel;
    }

    /**
     * Import the specified row of data from the sheet and return the related model
     *
     * @param int $row
     * @return \Illuminate\Database\Eloquent\Model|false
     * @throws \Exception
     */
    protected function importRow(int $row)
    {
        // Replace odd encodings
        $description = trim($this->resolve('Description', $row));
        $description = str_replace('–', '-', $description);

        return TellusTypecode::create([
            'category' => trim($this->resolve('Category', $row)),
            'code' => intval($this->resolve('Numeric Code (XML TC)', $row)),
            'subcategory' => trim($this->resolve('Subcategory', $row)),
            'text_code' => trim($this->resolve('Text Code', $row)),
            'description' => $description,
        ]);
    }

    /**
     * The message to show before executing the import
     *
     * @return string
     */
    protected function warningMessage()
    {
        return 'Importing Tellus Typecode Data Dictionary...';
    }

    /**
     * Return true if the row is empty or should be skipped
     *
     * @param int $row
     * @return bool
     * @throws \Exception
     */
    protected function emptyRow(int $row)
    {
        $code = $this->resolve('Numeric Code (XML TC)', $row);

        return ($code === null || trim($code) === '');
    }

    /**
     * Return the current business model for who the data should be imported in to
     * NOTE: Business Chain should be used for caregivers.  This is only for compatibility with business-only resources.
     *
     * @return \App\Business
     */
    protected function business()
    {
        return null;
    }
}
