<?php
namespace App\Http\Controllers\Admin;

use App\BusinessChain;
use App\Http\Controllers\Controller;
use App\OfficeUser;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\Resources\OfficeUser as OfficeUserResource;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfficeUserController extends Controller
{
    public function index(BusinessChain $chain)
    {
        $users = $chain->users->sortBy('name');

        return OfficeUserResource::collection($users);
    }

    public function store(Request $request, BusinessChain $chain)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'username' => 'nullable|unique:users',
            'firstname' => 'required',
            'lastname' => 'required',
            'password' => 'required|confirmed',
        ]);
        $data['password'] = bcrypt($data['password']);

        $request->validate(
            ['businesses' => 'required|array', 'businesses.*' => 'required|exists:businesses,id'],
            ['*' => 'An office user needs to be assigned at least one location.']
        );
        $businessIds = $request->businesses;

        \DB::beginTransaction();

        if ($user = $chain->users()->create($data)) {
            $user->businesses()->sync($businessIds);
            $user->update([
                'default_business_id' => $user->businesses->first()->id,
                'timezone' => $user->businesses->first()->timezone,
            ]);
            $resource = new OfficeUserResource($user);

            \DB::commit();
            return new CreatedResponse('The user has been created.', $resource->toArray($request));
        }

        \DB::rollBack();
        return new ErrorResponse(500, 'Unknown error');
    }

    public function show(Request $request, BusinessChain $chain, OfficeUser $user)
    {
        return $user;
    }

    public function update(Request $request, BusinessChain $chain, OfficeUser $user)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'username' => Rule::unique('users')->ignore($user->id),
            'firstname' => 'required',
            'lastname' => 'required',
        ]);

        $request->validate(
            ['businesses' => 'required|array', 'businesses.*' => 'required|exists:businesses,id'],
            ['*' => 'An office user needs to be assigned at least one location.']
        );
        $businessIds = $request->businesses;

        if ($password = $request->input('password')) {
            $request->validate(['password' => 'confirmed']);
            $data['password'] = bcrypt($password);
        }

        if ($user->update($data)) {
            $user->businesses()->sync($businessIds);
            return new SuccessResponse('The user has been updated.', $user->toArray());
        }

        return new ErrorResponse(500, 'Unknown error');
    }

    public function destroy(Request $request, BusinessChain $chain, OfficeUser $user)
    {
        \DB::beginTransaction();

        $user->user->update(['active' => false]);
        if ($user->delete()) {
            \DB::commit();
            return new SuccessResponse('The user has been deleted.');
        }
        
        return new ErrorResponse(500, 'Unknown error');
    }

}