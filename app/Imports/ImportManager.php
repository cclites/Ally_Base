<?php

namespace App\Imports;

class ImportManager
{
    protected static $providers = [
        'acorn'    => AcornImportProcessor::class,
        'sarasota' => SarasotaImportProcessor::class,
    ];

    /**
     * @param $provider
     * @param $file
     * @param $business
     * @return \App\Imports\ImportProcessor
     */
    public static function open($provider, $business, $file)
    {
        if (!array_key_exists($provider, self::$providers)) {
            throw new \Exception("$provider is not a valid import provider.");
        }
        return new self::$providers[$provider]($business, $file);
    }
}
