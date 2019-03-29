<?php

namespace App\Contracts;

interface SFTPWriterInterface
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

    function _close_handle($handle);
}
