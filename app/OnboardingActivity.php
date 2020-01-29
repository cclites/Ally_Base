<?php
namespace App;

/**
 * App\OnboardingActivity
 *
 * @property int $id
 * @property string $name
 * @property string $category
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardingActivity whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardingActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardingActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardingActivity whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardingActivity whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardingActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardingActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardingActivity query()
 */
class OnboardingActivity extends BaseModel
{
    protected $guarded = ['id'];
}
