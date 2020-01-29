<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\KnowledgeRole
 *
 * @property int $id
 * @property int $knowledge_id
 * @property string $role
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KnowledgeRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KnowledgeRole whereKnowledgeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KnowledgeRole whereRole($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KnowledgeRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KnowledgeRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\KnowledgeRole query()
 */
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
