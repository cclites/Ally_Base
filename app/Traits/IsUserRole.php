<?php
namespace App\Traits;

use App\Address;
use App\BankAccount;
use App\CreditCard;
use App\PhoneNumber;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

trait IsUserRole
{
    use SoftDeletes;

    /**
     * IsUserRole constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        // Automatically load user relationship
        if (empty($this->with)) $this->with = ['user'];
        // Automatically append the following attributes to Role Model's data output
        $this->append(['firstname', 'lastname', 'email', 'name', 'nameLastFirst']);
    }

    /**
     * Forward the magic getter to the related User model if property is not found in the Role model
     *
     * @param $name
     * @return null
     */
    public function __get($name) {
        $parentValue = parent::__get($name);
        if ($parentValue === null) {
            if (isset($this->attributes[$this->primaryKey])) return $this->user->$name ?? null;
        }
        return $parentValue;
    }

    /**
     * Get the name of this Role (e.g. App\Client returns Client)
     *
     * @return string
     */
    public function getRoleType()
    {
        return snake_case(class_basename(get_called_class()));
    }

    ///////////////////////////////////////////
    /// Related User
    ///////////////////////////////////////////

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    ///////////////////////////////////////////
    /// Name Concatenation Forwarders
    ///////////////////////////////////////////

    public function name()
    {
        return $this->user->name();
    }

    public function nameLastFirst()
    {
        return $this->user->nameLastFirst();
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getFirstNameAttribute()
    {
        return $this->user->firstname;
    }

    public function getLastNameAttribute()
    {
        return $this->user->lastname;
    }

    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    public function getNameAttribute()
    {
        return $this->name();
    }

    public function getNameLastFirstAttribute()
    {
        return $this->nameLastFirst();
    }

    ///////////////////////////////////////////
    /// Attribute Input Handling
    ///////////////////////////////////////////

    /**
     * Simplifies the fill process to avoid checking against guarded attributes in the Role model
     * This is needed because $fillable is used to define role attributes which the rest being forwarded to the related User model
     *
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes = [])
    {
        foreach($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Overridden Save Method to save $fillable attributes to the Role Model with the remaining attributes forwarded to the related User Model
     *
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
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

    ///////////////////////////////////////////
    /// Delete & Restore Forwarding
    ///////////////////////////////////////////

    public function delete()
    {
        $this->user->delete();
        return parent::delete();
    }

    public function forceDelete()
    {
        $this->user->forceDelete();
        return parent::forceDelete();
    }

    public function restore()
    {
        $this->user->restore();
        return SoftDeletes::restore();
    }

    ///////////////////////////////////////////
    /// Forwarded Relationship Methods
    ///////////////////////////////////////////

    public function addresses()
    {
        return $this->hasMany(Address::class, 'user_id', 'id');
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class, 'user_id', 'id');
    }

    public function creditCards()
    {
        return $this->hasMany(CreditCard::class, 'user_id', 'id');
    }

    public function phoneNumbers()
    {
        return $this->hasMany(PhoneNumber::class, 'user_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'user_id', 'id');
    }
}
