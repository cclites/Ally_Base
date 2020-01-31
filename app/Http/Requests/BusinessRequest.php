<?php
namespace App\Http\Requests;

use App\Business;
use App\BusinessChain;
use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Class BusinessRequest
 * @package App\Http\Requests
 *
 * This class should be extended for any office user request in which the entity requires a business_id
 */
abstract class BusinessRequest extends FormRequest
{
    /**
     * If true, preserve the validated data and don't add business_id to it.
     *
     * @var bool
     */
    protected $preserveValidated = false;

    /**
     * The array of rules to validate against
     *
     * @return array
     */
    abstract public function rules();

    /**
     * A default authorize() method to return true.  Authorization rules normally belong in a policy, called from the controller.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * A default filtered() method to return validated data that has been properly filtered and formatted
     * Notes: Extend this method to add formatting to dates and other fields
     *
     * @return array
     */
    public function filtered()
    {
        return $this->validated();
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $this->addBusinessIdRule($validator);
    }

    /**
     * Automatically add the business_id for single business users to the validation data
     *
     * @return array
     */
    public function validationData()
    {
        return $this->addBusinessInput($this->all());
    }

    /**
     * Automatically add the business_id for single business users to the validation data
     *
     * @return array
     */
    public function validated()
    {
        if ($this->preserveValidated) {
            return parent::validated();
        }

        return $this->addBusinessInput(parent::validated());
    }

    /**
     * Get the business chain from the request.
     *
     * @return BusinessChain
     */
    public function getChain() : BusinessChain
    {
        return $this->getBusiness()->chain;
    }

    /**
     * Add the validation rule for business_id to
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    protected function addBusinessIdRule(Validator $validator)
    {
        if (!array_key_exists('business_id', $this->rules())) {
            $validator->addRules([
                'business_id' => $this->getBusinessRulesForUser(),
            ]);
            $validator->setCustomMessages([
                'business_id.required' => 'You must select a business location.',
                'business_id.in' => 'You do not have access to the selected business location.',
                'business_id.*' => 'Unknown business location identifier.',
            ]);
            $validator->after(function(Validator $validator) {
                if (!empty($validator->failed()['business_id']['In'])) {
                    abort(403, 'You do not have access to the selected business.');
                }
            });
        }
    }

    /**
     * The rules for business_id for a specified user
     *
     * @param \App\User|null $user
     * @return array
     */
    protected function getBusinessRulesForUser(User $user = null)
    {
        if (!$user) $user = \Auth::user();

        if ($user->role_type === 'admin') {
            return ['required', 'exists:businesses,id'];
        }

        $businessIds = $user->getBusinessIds();
        $rules = [
            'required',  // may be replaced with 'required' later
            'integer',
            'in:' . implode(',', $businessIds)
        ];

        return $rules;
    }

    protected function addBusinessInput(array $original)
    {
        return array_merge($original, [
            'business_id' => $this->getBusinessId(),
        ]);
    }
}