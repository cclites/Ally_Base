<?php

namespace App\Imports;

interface ImportProcessor
{
    /**
     * @param $file
     * @return \Illuminate\Support\Collection
     */
    function read($file);
}
