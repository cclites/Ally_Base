<?php

namespace App;

use OwenIt\Auditing\Models\Audit as BaseAudit;

/**
 * App\Audit
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $event
 * @property int $auditable_id
 * @property string $auditable_type
 * @property array $old_values
 * @property array $new_values
 * @property string|null $url
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $tags
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $auditable
 * @property-read void $auditable_title
 * @property-read void $diff
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Audit whereAuditableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Audit whereAuditableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Audit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Audit whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Audit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Audit whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Audit whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Audit whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Audit whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Audit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Audit whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Audit whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Audit whereUserId($value)
 * @mixin \Eloquent
 */
class Audit extends BaseAudit
{
    /**
     * Attributes to auto load on every model.
     *
     * @var array
     */
    protected $appends = ['diff', 'auditable_title'];

    /**
     * Get 'title' of model.
     *
     * @return void
     */
    public function getAuditableTitleAttribute()
    {
        return substr($this->auditable_type, 0, 4) == 'App\\' ? substr($this->auditable_type, 4) : $this->auditable_type;
    }

    /**
     * Attribute that gets the modified values.
     *
     * @return void
     */
    public function getDiffAttribute()
    {
        return $this->getModified();
    }

}
