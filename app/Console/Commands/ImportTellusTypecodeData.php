<?php

namespace App\Console\Commands;

use App\Services\TellusService;
use App\TellusEnumeration;
use App\TellusTypecode;

class ImportTellusTypecodeData extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tellus-typecode-data {schema_file?} {dictionary_file?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and update values from a Tellus Typecode Data Dictionary file.';

    /** @var string */
    protected $dictionaryFile = '';

    /** @var string */
    protected $schemaFile = '';

    /**
     * XSD Validation File can be found at: https://tellusolutions.atlassian.net/wiki/spaces/EVV/pages/182124545/Rendered+Services+File+Specifications
     * Typecode Dictionary can be found at: https://tellusolutions.atlassian.net/wiki/spaces/EVV/pages/591527967/Type+Code+Listing+for+all+Files
     *
     * @return mixed|void
     */
    public function handle()
    {
        if ($file = $this->argument('dictionary_file')) {
            $this->dictionaryFile = $file;
        }

        if ($file = $this->argument('schema_file')) {
            $this->schemaFile = $file;
        }

        // Fetch latest files if none specified.
        if (empty($this->schemaFile) || empty($this->dictionaryFile)) {
            $this->info("Fetching Tellus resource files...");
            if (!TellusService::downloadApiResources()) {
                $this->error('Could not download Tellus resources, exiting');
                return 1;
            }
            $this->info("Tellus resources updated.");

            if (empty($this->schemaFile)) {
                $this->schemaFile = \Storage::disk('public')->path(TellusService::XML_SCHEMA_FILENAME);
            }

            if (empty($this->dictionaryFile)) {
                $this->dictionaryFile = \Storage::disk('public')->path(TellusService::TYPECODE_DICTIONARY_FILENAME);
            }
        }

        $this->info("Importing new list of Tellus Enumerations...");
        \DB::table('tellus_enumerations')->truncate();
        $enumerations = $this->importEnumerationsFromSchema($this->schemaFile);
        $this->info("Imported {$enumerations} Tellus Enumerations.");

        // Handle importing typecodes from BaseImporter
        \DB::table('tellus_typecodes')->truncate();
        parent::handle();

        $this->info('Cleaning known dictionary issues...');
        $this->cleanKnownDictionaryIssues();

        $this->info("Operation complete.");
    }

    /**
     * At the time of of this code 9/26/2019 the tellus typecode dictionary
     * has errors and mismatches from the XSD schema.  This will handle
     * fixing those errors automatically.
     *
     */
    public function cleanKnownDictionaryIssues()
    {
        // tellus seems to have fixed this..
        // TellusTypecode::where('category', 'Payer')
        //             ->where('text_code', 'SUNS')
        //             ->update(['description' => 'Sunshine/Centine']);

        // tellus seems to have fixed this..
        // TellusTypecode::where('category', 'Plan')
        //             ->where('text_code', 'FMSP')
        //             ->update(['description' => 'Florida Medicaid State Plan']);

        // tellus seems to have fixed this..
        // TellusTypecode::where('category', 'Payer')
        //             ->where('text_code', 'UHTH')
        //             ->update(['description' => 'United HealthCare']);

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
            $textCodes = $xpath->evaluate("/xs:schema/xs:simpleType[@name='ST_{$category}']/xs:restriction/xs:enumeration");

            if (count($enums) == 0) {
                continue;
            }

            if (count($enums) != count($textCodes)) {
                die("Mismatch enumeration count for $category");
            }

            for ($i = 0; $i < count($enums); $i++) {
                TellusEnumeration::create([
                    'category' => $category,
                    'code' => $enums[$i]->getAttribute('value'),
                    'value' => $textCodes[$i]->getAttribute('value'),
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
        return 'Importing Tellus typecode data dictionary...';
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
