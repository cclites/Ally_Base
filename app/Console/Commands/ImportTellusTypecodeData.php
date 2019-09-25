<?php

namespace App\Console\Commands;

use App\TellusEnumeration;
use App\TellusTypecode;

class ImportTellusTypecodeData extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tellus-typecode-data {enumeration?} {file?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and update values from a Tellus Typecode Data Dictionary file.';

    /**
     * 
     * Known Errors with Dictionary:
     *  Format is: Category :: Text_Code :: Description
     * 
     *  1. Payer :: SUNS :: Sunshine State Health Plan Inc. of Florida => change Description to "Sunshine/Centine"
     *  2. Plan :: FMSP :: Florida State Medical Plan => change Description to "Florida Medicaid State Plan"
     *  3. Payer :: UHTH :: UnitedHealthcare of Florida => change Description to United HealthCare
     * 
     * 
     * !!! ALSO, scan all ReasonCodes ( and other values ) for dashes ( - ). There are a few that are improperly encoded and should be just replaced with a regular "-"
     *
     * Files for parameters are located at: https://tellusolutions.atlassian.net/wiki/spaces/EVV/pages/182124545/Rendered+Services+File+Specifications
     */

    /**
     * @return mixed|void
     */
    public function handle()
    {
        \DB::table('tellus_typecodes')->truncate();
        \DB::table('tellus_enumerations')->truncate();

        $this->info("Downloading latest xsd validation data...");

        // this populates the 'enumerations table'. The argument named 'file' populates the typecodes table
        $file = $this->argument( 'enumeration' ) ?? "https://tellusolutions.atlassian.net/wiki/download/attachments/182124545/Rendered%20Services%20v2%20XML%20Schema%2020190920.xsd?api=v2";
        $xsd = file_get_contents( $file );

        $this->info("Importing new list of Tellus enumerations...");

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

        $this->info("Imported $count Tellus Validation rules.");

        parent::handle();
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
        return TellusTypecode::create([
            'category' => trim($this->resolve('Category', $row)),
            'code' => intval($this->resolve('Numeric Code (XML TC)', $row)),
            'subcategory' => trim($this->resolve('Subcategory', $row)),
            'text_code' => trim($this->resolve('Text Code', $row)),
            'description' => trim($this->resolve('Description', $row)),
        ]);
    }

    /**
     * The message to show before executing the import
     *
     * @return string
     */
    protected function warningMessage()
    {
        return 'Importing typecode data dictionary...';
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
