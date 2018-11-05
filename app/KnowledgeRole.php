<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KnowledgeRole extends Model
{
    protected $guarded = ['id'];

    public $timestamps = false;

    public $table = 'knowledge_roles';
}
