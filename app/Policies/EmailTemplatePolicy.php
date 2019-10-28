<?php

namespace App\Policies;

use App\User;
use App\EmailTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailTemplatePolicy extends BasePolicy
{
    /**
     * Determine whether the user can create emailTemplates.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user, $data)
    {
        $template = new EmailTemplate($data);
        return $this->businessCheck($user, $template);
    }

    /**
     * Determine whether the user can view the emailTemplate.
     *
     * @param  \App\User  $user
     * @param  \App\EmailTemplate  $emailTemplate
     * @return mixed
     */
    public function read(User $user, EmailTemplate $emailTemplate)
    {
        return $this->businessCheck($user, $emailTemplate);
    }



    /**
     * Determine whether the user can update the emailTemplate.
     *
     * @param  \App\User  $user
     * @param  \App\EmailTemplate  $emailTemplate
     * @return mixed
     */
    public function update(User $user, EmailTemplate $emailTemplate)
    {
        return $this->businessCheck($user, $emailTemplate);
    }

    /**
     * Determine whether the user can delete the emailTemplate.
     *
     * @param  \App\User  $user
     * @param  \App\EmailTemplate  $emailTemplate
     * @return mixed
     */
    public function delete(User $user, EmailTemplate $emailTemplate)
    {
        return $this->businessCheck($user, $emailTemplate);
    }
}
