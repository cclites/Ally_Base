<?php
namespace App\Services;

use phpseclib\Net\SFTP;
use App\Contracts\SFTPWriterInterface;

/**
 * Class DummySFTPWriter
 *
 * Used for local and testing environments to just write to the storage/sftp directory on SFTP put requests
 *
 * @package App\Services
 */
class DummySFTPWriter extends SFTP implements SFTPWriterInterface
{
    function __construct(string $host, int $port = 22, int $timeout = 10)
    {}

    function login($username)
    {
        return true;
    }

    function put(
        $remote_file,
        $data,
        $mode = self::SOURCE_STRING,
        $start = -1,
        $local_start = -1,
        $progressCallback = null
    ) {
        @mkdir(storage_path('sftp'));
        $path = storage_path('sftp' . DIRECTORY_SEPARATOR . $remote_file);
        switch($mode) {
            case self::SOURCE_LOCAL_FILE:
                return \File::copy($data, $path);
            default:
                return \File::put($path, $data);
        }
    }

    function _close_handle($handle)
    {
        return true;
    }

    function __destruct()
    {}
}
