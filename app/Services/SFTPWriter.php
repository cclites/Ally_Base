<?php

namespace App\Services;

use phpseclib\Net\SFTP;
use App\Contracts\SFTPWriterInterface;

class SFTPWriter extends SFTP implements SFTPWriterInterface
{
}
