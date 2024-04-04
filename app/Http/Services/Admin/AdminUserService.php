<?php

namespace App\Http\Services\Admin;

use Exception;
use App\Models\Role;
use App\Http\Services\EncryptService;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;

class AdminUserService
{
    #region User
    public function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->Where('first_name', 'like', '%' . strtolower($search) . '%')
                    ->orWhere('last_name', 'like', '%' . strtolower($search) . '%')
                    ->orWhere('email', 'like', '%' . strtolower($search) . '%');
            });
        }
        return $query;
    }

    public function __construct(EncryptService $encryptService)
    {
        // $this->versionService = $versionService;
        $this->encryptService = $encryptService;
    }

    public function create($request)
    {
        try {
            // Validate the request data
            $request->validate([$request->all()]);

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

            // if ($user) {
            //     $data = [
            //         'user' => $user,
            //         'token' => $user->createToken('Api Token of ' . $user->email)->plainTextToken,
            //     ];

                // Asynchronously dispatch the Registered event and return the user data and token
                new Registered($user);
                // $this->dispatch(new Registered($user));
                return $user;
            // } else {
            //     throw new Exception('Something went wrong when creating the user account');
            // }
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ]));
        }
    }
    public function search($query, $request)
    {
        if (!empty($request['search'])) {
            $search = $request['search'];
            $query->where(function ($query) use ($search) {
                $query->Where('first_name', 'like', '%' . strtolower($search) . '%')
                    ->orWhere('last_name', 'like', '%' . strtolower($search) . '%')
                    ->orWhere('email', 'like', '%' . strtolower($search) . '%');

                // Code above only work with PostgreSQL that use for deploy
                // $query->whereRaw('LOWER(`name`) LIKE ? ',['%'.$search.'%'])
                //     ->orWhereRaw('LOWER(`description`) LIKE ? ',['%'.$search.'%'])
                //     ->orWhereRaw('LOWER(`compatible`) LIKE ? ',['%'.$search.'%']);
            });
        }

        return $query;
    }

    public function getall($request)
    {
        $query = User::with(['roles:name'])->latest()->select('id', 'first_name', 'last_name', 'email', 'status');

        $query = self::search($query, $request);

        return $query->paginate(!empty($request['pageSize']) ? (int)$request['pageSize'] : config('app.pagination'));
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

    #region Permission
    public function addPermisisonToRole($request, $role_id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                throw new Exception('User not found');
            };

            $role = Role::where('id', $role_id)->first();

            if (!$role) {
                throw new Exception('Role not valid');
            }
            return $role->givePermissionTo($request->permissions);
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->error($ex->getMessage()));
        }
    }

    public function getAllPermissionOfRole($request, $role_id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                throw new Exception('User not found');
            };

            $role = Role::where('id', $role_id)
                ->orWhere('name', $role_id)->first();

            if (!$role) {
                throw new Exception('Role is not valid');
            }

            $role->loadMissing('permissions');

            return $role;
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->error($ex->getMessage()));
        }
    }

    public function revokePermissionOfRole($request, $role_id, $permission_id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                throw new Exception('User not found');
            };

            $role = Role::where('id', $role_id)
                ->orWhere('name', $role_id)->first();

            if (!$role) {
                throw new Exception('Role is not valid');
            }

            return $role->revokePermissionTo($permission_id);
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->error($ex->getMessage()));
        }
    }

    #endregion
}
