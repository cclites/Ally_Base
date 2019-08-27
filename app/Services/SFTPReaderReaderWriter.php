<?php

namespace App\Services;

use phpseclib\Net\SFTP;
use App\Contracts\SFTPReaderWriterInterface;

class SFTPReaderReaderWriter extends SFTP implements SFTPReaderWriterInterface
{
}
