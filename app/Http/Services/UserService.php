<?php

namespace App\Http\Services;

use Exception;
use App\Models\User;
use App\Http\Services\FileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserService
{
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }


    public function changeInfo($request): User
   {
        try {
            $user = Auth::user();
            #Update user info
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->profession = $request->profession;
            $user->location = $request->location;
            $user->save();
            return $user;
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->error($ex->getMessage()));
        }
    }


    public function getInfo($request)
    {
        $user = Auth::user();

        return $user;
    }

    public function changeAvatar($request)
    {
        try {
            $user = Auth::user();

            if ($request->hasFile('avatar')) {
                $file = $this->fileService->storePublicFile(
                    'public/avatar',
                    $request->file('avatar')
                );
            } else {
                throw new Exception('Error occurred when change your avatar image');
            }

            $user->avatar = $file;
            $user->save();

            return $user->avatar;
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->error($ex->getMessage()));
        }
    }


    public function notify($request) {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        return $user->notifications()->paginate(!empty($request['pageSize']) ? (int)$request['pageSize'] : config('app.pagination'));
    }

    public function notifyMarkRead($request) {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if (!$request->ids) {
            return false;
        }

        $array = explode(',', $request->ids);

        foreach ($user->unreadNotifications as $notification) {
            if (in_array($notification->id, $array)){
                $notification->markAsRead();
            }
        }

        return true;
    }
}
