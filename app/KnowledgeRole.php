<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KnowledgeRole extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'knowledge_roles';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
