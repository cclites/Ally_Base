<?php
namespace App\Traits;

/**
 * Trait CanImpersonate
 * @package App\Traits
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait CanImpersonate
{
    use \Bizhub\Impersonate\Traits\CanImpersonate;

    /**
     * Get an array of the currently authenticated user with impersonation details
     *
     * @return array|null
     */
    public function withImpersonationDetails()
    {
        if (\Auth::id() !== $this->id) return null; // This is only used for the currently authenticated user

        return $this->toArray() + [
            'impersonating' => $this->isImpersonating(),
            'impersonator' => optional($this->impersonator())->toArray(),
        ];
    }
}