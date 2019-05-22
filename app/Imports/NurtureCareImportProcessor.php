<?php


namespace App\Imports;


final class NurtureCareImportProcessor extends AcornImportProcessor
{
    /**
     * @var float
     */
    public $overTimeMultiplier = 1.0;

    /**
     * Return a text based description that summarizes what fields/techniques this import processor uses
     *
     * @return string
     */
    function getDescription()
    {
        return "The Nurture Care format extends the Acorn Import Format but sets the OT multiplier to 1 (disabled):\n" . parent::getDescription() . "\nOverridden Overtime Multiplier: 1.0 (disabled)";
    }
}