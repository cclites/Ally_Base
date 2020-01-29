<?php

namespace App\Contracts;

interface SFTPReaderWriterInterface
{
    function login($username);

    function put(
        $remote_file,
        $data,
        $mode = self::SOURCE_STRING,
        $start = -1,
        $local_start = -1,
        $progressCallback = null
    );

    function get(
        $remote_file,
        $local_file = false,
        $offset = 0,
        $length = -1,
        $progressCallback = null
    );

    function _close_handle($handle);
}
