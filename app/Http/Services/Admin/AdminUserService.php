<?php

namespace App\Http\Services\Admin;

use Exception;
use App\Models\Role;
use App\Http\Services\EncryptService;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Http\Exceptions\HttpResponseException;

class AdminUserService
{
    #region User
    public function __construct(EncryptService $encryptService)
    {
        // $this->versionService = $versionService;
        $this->encryptService = $encryptService;
    }

    public function create($request)
    {
        try {
            // Validate the request data
            $request->validate([
                'username' => 'required|min:4|max:25|unique:users|regex:/(^[a-zA-Z]+[a-zA-Z0-9\\-]*$)/u',
                'email' => 'required|string|unique:users|email:rfc,dns,filter|max:255',
                'password' => ['required', 'confirmed', Rules\Password::default(), 'max:255'],
                'role_id' => 'required|exists:roles,id',
            ]);

            // Generate an API key for the user
            $apikey = $this->encryptService->apikeyGen();

            // Create the user
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'apikey' => $apikey,
            ]);

            // Attach the selected role to the user
            $user->roles()->attach($request->role_id);

            if ($user) {
                $data = [
                    'user' => $user,
                    'token' => $user->createToken('Api Token of ' . $user->email)->plainTextToken,
                ];

                // Asynchronously dispatch the Registered event and return the user data and token
                new Registered($user);
                // $this->dispatch(new Registered($user));
                return $data;
            } else {
                throw new Exception('Something goes wrong when create your account');
            }
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ], 422));
        }
    }

    public function getall($request)
    {
        $username = $request->input('username');
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $email = $request->input('email');
        $orderBy = $request->input('order_by', 'id');
        $sortBy = $request->input('sort_by', 'asc');


        $user = User::with(['roles:name'])
            ->select('id', 'username', 'first_name', 'last_name', 'email', 'status')
            ->orderBy($orderBy, $sortBy);

        if ($username) {
            $user = $user->where('username', 'like', '%' . $username . '%');
        }

        if ($first_name) {
            $user = $user->where('first_name', 'like', '%' . $first_name . '%');
        }

        if ($last_name) {
            $user = $user->where('last_name', 'like', '%' . $last_name . '%');
        }

        if ($email) {
            $user = $user->where('email', 'like', '%' . $email . '%');
        }

        return $user->paginate(!empty($request['pageSize']) ? (int)$request['pageSize'] : config('app.pagination'));
    }

    public function getNewUsersCount()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        return User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
    }

    public function getTotalUsers()
    {
        return User::count();
    }

    // This function bans a user by setting their status to 0.
    public function ban($request, $id)
    {
        try {

            if (!empty($id)) {
                $user = User::where('id', $request->id)->first();

                $user->status = 0;
                $user->save();

                return true;
            }

            return false;
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->error($ex->getMessage()));
        }
    }

    // This function un-bans a user by setting their status to 1.
    public function unban($request, $id)
    {
        try {

            if (!empty($id)) {
                $user = User::where('id', $id)->first();

                $user->status = 1;
                $user->save();

                return true;
            }

            return false;
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->error($ex->getMessage()));
        }
    }
    #endregion

    #region Role
    public function getRole($request)
    {
        return Role::all();
    }

    public function createRole($request)
    {
        return Role::create([
            'name' => $request->role_name,
        ]);
    }

    public function deleteRole($request)
    {
    }

    public function setRole($request, $id)
    {
        try {
            $user = User::where('id', $id)->first();

            if (!$user) {
                throw new Exception('User not found');
            };

            return $user->assignRole($request->role);
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->error($ex->getMessage()));
        }
    }

    public function  revokeRole($request, $user_id, $role_id)
    {
        try {
            $user = User::where('id', $user_id)->first();

            if (!$user) {
                throw new Exception('User not found');
            };

            return $user->removeRole($role_id);
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->error($ex->getMessage()));
        }
    }
    #endregion
}
