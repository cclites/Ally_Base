<?php

namespace App\Imports;

class AcornImportProcessor implements ImportProcessor
{

    function read($file)
    {
        $phpExcel = \PHPExcel_IOFactory::load($file);
        $collection = collect();

        // Map headers row

        // Loop through each line item and push to collection

        return $collection;
    }
}
