<?php
namespace App\Shifts\Data;

class TVVData
{
    /**
     * @var string
     */
    public $phoneNumber;
    /**
     * @var bool
     */
    public $verified;

    public function __construct(
        string $phoneNumber,
        bool $verified
    ) {
        $this->phoneNumber = $phoneNumber;
        $this->verified = $verified;
    }
}