<?php
namespace App\Traits;

use Crypt;

/**
 * Trait HasSSNAttribute
 * @package App\Traits
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasSSNAttribute
{
    /**
     * Encrypt ssn on entry
     *
     * @param $value
     */
    public function setSsnAttribute($value)
    {
        $this->attributes['ssn'] = $value ? Crypt::encrypt($value) : null;
    }

    /**
     * Decrypt ssn on retrieval
     *
     * @return null|string
     */
    public function getSsnAttribute()
    {
        return empty($this->attributes['ssn']) ? null : Crypt::decrypt($this->attributes['ssn']);
    }

    /**
     * Encrypt ssn on entry
     *
     * @param $value
     */
    public function setw9SsnAttribute($value)
    {
        $this->setSsnAttribute($value);
    }

    /**
     * Decrypt ssn on retrieval
     *
     * @return null|string
     */
    public function getw9SsnAttribute()
    {
        return $this->getSsnAttribute();
    }

    /**
     * @return string
     */
    public function getMaskedSsnAttribute()
    {
        return $this->ssn ? '***-**-' . substr($this->ssn, -4) : "";
    }
}