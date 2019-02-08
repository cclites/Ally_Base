<?php
namespace App\Billing;

use Illuminate\Database\Eloquent\Model;
use App\BusinessChain;

trait PaymentLogFunctions
{
    protected static $lockHandle;

    /**
     * Return the next batch ID
     *
     * @param int|null $chainId
     * @return string
     */
    public static function getNextBatch(?int $chainId): string
    {
        $prefix = (self::$batchPrefix ?? '') . str_pad((string) $chainId, 8, "0", STR_PAD_LEFT);
        return uniqid($prefix);
    }

    public static function getBatches(?int $chainId = null): array
    {
        $query = self::groupBy('batch_id')->orderBy('created_at', 'DESC');
        if ($chainId) {
            $query->where('chain_id', $chainId);
        }

        return $query->pluck('batch_id')->toArray();
    }

    public static function acquireLock()
    {
        $handle = self::openLockFile();
        if (!$handle) {
            throw new \Exception("Cannot open lock file " . self::lockFilePath() . ".  Check permissions.");
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            throw new \Exception("Cannot acquire lock on lock file " . self::lockFilePath());
        }
    }

    public static function releaseLock()
    {
        $handle = self::openLockFile();
        if (!$handle) {
            return;
        }

        flock($handle, LOCK_UN);
    }

    private static function lockFilePath()
    {
        return sys_get_temp_dir() . "/payment_log.lock";
    }

    private static function openLockFile()
    {
        if (!self::$lockHandle) {
            self::$lockHandle = fopen(self::lockFilePath() , 'a+');
        }
        return self::$lockHandle;
    }


    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    public function chain()
    {
        return $this->belongsTo(BusinessChain::class, 'chain_id');
    }

    public function method()
    {
        return $this->morphTo('payment_method');
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    /**
     * Set string attributes based on an Exception instance
     *
     * @param \Exception $e
     */
    public function setException(\Exception $e): void
    {
        $this->exception = get_class($e);
        $this->error_message = $e->getMessage();
    }

    public function setPaymentMethod($paymentMethod): void
    {
        if ($paymentMethod instanceof Model) {
            $this->method()->associate($paymentMethod);
        }
    }
}