<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class GenericExport implements FromCollection
{
    /**
     * @var Collection
     */
    protected $data;

    /**
     * @var Collection
     */
    protected $headers;

    /**
     * GenericExport Constructor.
     * @param iterable $data
     */
    public function __construct(iterable $data)
    {
        $this->data = collect($data);

        if ($this->data->count() >= 1) {
            $this->headers = collect()->push(array_keys($this->data[0]));
        } else {
            $this->headers = collect();
        }
    }

    public function collection()
    {
        return $this->headers->merge($this->data);
    }
}