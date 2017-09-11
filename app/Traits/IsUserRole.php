<?php
namespace App\Traits;

use App\User;

trait IsUserRole
{
    public function getRoleType()
    {
        return snake_case(class_basename(get_called_class()));
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    public function __get($name) {
        $parentValue = parent::__get($name);
        if ($parentValue === null) {
             if (isset($this->attributes[$this->primaryKey])) return $this->user->$name ?? null;
        }
        return $parentValue;
    }

    public function fill(array $attributes = [])
    {
        foreach($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    public function save(array $options = [])
    {
        $this->setIncrementing(false);

        $role_attributes = array_intersect_key($this->attributes, array_flip($this->fillable));
        $user_attributes = array_diff_key($this->attributes, array_flip($this->fillable));

        if ($this->id) {
            $user = $this->user;
            $user->update($user_attributes);
        }
        else {
            $user = User::forceCreate(array_merge(
                $user_attributes,
                ['role_type' => $this->getRoleType()]
            ));
        }

        if (!$user) {
            throw new \Exception('Unable to create user from role model.');
        }

        $this->attributes = array_merge(
            $role_attributes,
            ['id' => $user->id]
        );

        return parent::save($options);
    }
}
