<?php

namespace App\Services;

class TellusValidationException extends \Exception
{
    /**
     * @var array
     */
    protected $errors = [];

    function __construct(string $message, array $errors = null)
    {
        $this->message = $message;
        $this->code = 0;
        $this->errors = $errors;
    }

    public function getErrors() : array
    {
        $errors = collect([]);
        collect($this->errors)->each(function (array $error) use ($errors) {

            if (isset($error['field'])) {

                switch ($error['field']) {
                    case 'ProviderEin':
                        $error['field'] = 'Business EIN #';
                        $error['url'] = route('business.settings.index').'#medicaid';
                        break;
                    case 'ProviderMedicaidId':
                        $error['field'] = 'Business Medicaid ID';
                        $error['url'] = route('business.settings.index').'#medicaid';
                        break;
                    case 'ProviderNPI':
                        $error['field'] = 'Business NPI Number';
                        $error['url'] = route('business.settings.index').'#medicaid';
                        break;
                    case 'ProviderNPITaxonomy':
                        $error['field'] = 'Business NPI Taxonomy';
                        $error['url'] = route('business.settings.index').'#medicaid';
                        break;
                    case 'ProviderNPIZipCode':
                        $error['field'] = 'Business Zipcode';
                        $error['url'] = route('business.settings.index').'#phone';
                        break;
                }

            }

            if ($errors->where('field', '=', $error['field'])
                ->where('error', '=', $error['error'])
                ->count() > 0
            ) {
                return;
            }

            $errors->push($error);
        });

        return $errors->toArray();
    }

    public function hasErrors() : bool
    {
        return $this->errors ? count($this->errors) > 0 : false;
    }

}
