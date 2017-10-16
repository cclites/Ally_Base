<?php

namespace App\Traits;

/**
 * Class HiddenIdTrait
 * @package Packages\HiddenID
 *
 */
trait HiddenIdTrait
{
    protected static $hiddenIdPassword = 'A1B2C3D4E5F6G7H8';
    protected static $algorithm = 'bf-cbc';

    /**
     * Encrypt a primary key
     *
     * @param int|string $key
     *
     * @return mixed
     * @throws \Exception
     */
    public static function encryptKey($key)
    {
        $password = self::$hiddenIdPassword;
        $str      = base64_encode(
            openssl_encrypt($key, self::$algorithm, self::getOnlyPassword($password), 0, self::getOnlyIv($password))
        );

        return $str;
    }

    /**
     * Decrypt an encrypted key to its original primary key
     *
     * @param string $key
     *
     * @return mixed
     */
    public static function decryptKey($key)
    {
        $password = self::$hiddenIdPassword;

        $str = openssl_decrypt(
            base64_decode($key), self::$algorithm, self::getOnlyPassword($password), 0, self::getOnlyIv($password)
        );

        return $str;
    }

    /**
     * Find a model by its encrypted primary key.
     *
     * @param  string $key
     * @param  array $columns
     *
     * @return $this
     */
    public static function findEncrypted($key, $columns = array('*'))
    {
        $id = self::decryptKey($key);

        return self::find($id, $columns);
    }

    /**
     * Find a model by its encrypted primary key or throw an Exception
     *
     * @param string $key
     * @param array $columns
     *
     * @return mixed
     * @throws \Exception
     */
    public static function findEncryptedOrFail($key, $columns = array('*'))
    {
        $id = self::decryptKey($key);

        return self::findOrFail($id, $columns);
    }

    /**
     * Return the encrypted key of the Model
     *
     * @return string
     */
    public function getEncryptedKey()
    {
        $primaryKey = ($this->primaryKey) ? $this->primaryKey : 'id';
        if ( ! isset($this->attributes[$primaryKey])) {
            return null;
        }

        return $this->encryptKey($this->attributes[$primaryKey]);
    }

    protected static function getOnlyPassword($password)
    {
        if (strlen($password) < 16) {
            throw new \Exception('HiddenIdException: Password must be at least 16 bytes');
        }

        return substr($password, 0, -8);
    }

    protected static function getOnlyIv($password)
    {
        return substr($password, -8);
    }
}
