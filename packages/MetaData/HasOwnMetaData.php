<?php

namespace Packages\MetaData;

/**
 * Trait HasOwnMetaData
 * This trait can be used instead of HasMetaData if you want a dedicated model/table for meta data
 *
 * @package Packages\MetaData
 * @author Devon Bessemer
 *
 */
trait HasOwnMetaData
{
    use HasMetaData;

    /**
     * Generate the valid dedicated meta class, ex. App\User => App\UserMeta
     *
     * @return string
     */
    public function getOwnMetaClass()
    {
        $class = get_called_class();
        return $class . 'Meta';
    }
}