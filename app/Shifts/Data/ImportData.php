<?php


namespace App\Shifts\Data;


use App\Import;
use App\Shifts\Contracts\ShiftDataInterface;

class ImportData implements ShiftDataInterface
{
    public $import;

    public function __construct(Import $import)
    {
        $this->import = $import;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'import_id' => $this->import->id,
        ];
    }
}