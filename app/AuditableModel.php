<?php
namespace App;

use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class AuditableModel
 * @package App
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 */
abstract class AuditableModel extends BaseModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
}