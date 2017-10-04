<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /**
     * User who uploaded the document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
