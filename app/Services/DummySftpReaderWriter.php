<?php
namespace App\Services;

use phpseclib\Net\SFTP;
use App\Contracts\SFTPReaderWriterInterface;

/**
 * Class DummySFTPWriter
 *
 * Used for local and testing environments to just write to the storage/sftp directory on SFTP put requests
 *
 * @package App\Services
 */
class DummySFTPReaderWriter extends SFTP implements SFTPReaderWriterInterface
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

    function get(
        $remote_file,
        $local_file = false,
        $offset = 0,
        $length = -1
    ) {
        $path = storage_path('sftp' . DIRECTORY_SEPARATOR . $remote_file);

        if (! file_exists($path)) {
            return null;
        }

        $data = file_get_contents($path);

        if (! $local_file) {
            return $data;
        } else {
            return \File::put($local_file, $data);
        }
    }

    function _close_handle($handle)
    {
        return true;
    }

    function __destruct()
    {}
}
