<?php

namespace App;

use OwenIt\Auditing\Models\Audit as BaseAudit;

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
