<?php
namespace App\Http\Controllers\Admin;

use App\Business;
use App\Http\Controllers\Controller;
use App\OfficeUser;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfficeUserController extends Controller
{
    public function index(Business $business)
    {
        return $business->users->sortBy('name');
    }

    public function store(Request $request, Business $business)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'username' => 'nullable|unique:users',
            'firstname' => 'required',
            'lastname' => 'required',
            'password' => 'required|confirmed',
        ]);
        $data['password'] = bcrypt($data['password']);

        if ($user = $business->users()->create($data)) {
            return new CreatedResponse('The user has been created.', $user->toArray());
        }

        return new ErrorResponse(500, 'Unknown error');
    }

    public function show(Request $request, Business $business, OfficeUser $user)
    {
        return $user;
    }

    public function update(Request $request, Business $business, OfficeUser $user)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'username' => Rule::unique('users')->ignore($user->id),
            'firstname' => 'required',
            'lastname' => 'required',
        ]);

        if ($password = $request->input('password')) {
            $request->validate(['password' => 'confirmed']);
            $data['password'] = bcrypt($password);
        }

        if ($user->update($data)) {
            return new SuccessResponse('The user has been updated.', $user->toArray());
        }

        return new ErrorResponse(500, 'Unknown error');
    }

    public function destroy(Request $request, Business $business, OfficeUser $user)
    {
        if ($user->delete()) {
            return new SuccessResponse('The user has been deleted.');
        }
        
        return new ErrorResponse(500, 'Unknown error');
    }

}