<?php

namespace App\Http\Services\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Services\EncryptService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Notifications\NewLoginNotification;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;



class AuthService
{
    use DispatchesJobs;

    public function __construct(EncryptService $encryptService)
    {
        // $this->versionService = $versionService;
        $this->encryptService = $encryptService;
    }

    public function create($request)
    {
        try {
            //Function
            $request->validated($request->all());

            // Generate an API key for the user
            $apikey = $this->encryptService->apikeyGen();

            // Generate 2fa code
            // $google2FA = new Google2FA();

            // $gg2fa = $google2FA->generateSecretKey();

            // Create the user and attach the role with ID 1
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'apikey' => $apikey,
            ]);

            $user->roles()->attach(1);

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
            throw new HttpResponseException(response()->json(
                [
                    'success' => false,
                    'message' =>
                    $ex->getMessage(),
                ],
            ));
        }
    }

    public function login($request)
    {
        try {

            $request->validated($request->all());
            $user = User::where('username', $request->username)->with("roles")->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw new Exception('Wrong password');
            }

            if ($user->status == 0) {
                throw new Exception('Your Account is suspended, please contact Admin.');
            }

            $data = [
                'user' => $user,
                'token' => $user->createToken('Api Token of ' . $user->email)->plainTextToken,
            ];

            Notification::send($user, new NewLoginNotification($user, $request));

            return $data;
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->json(
                [
                    'success' => false,
                    'message' =>
                    $ex->getMessage(),
                ],
                401
            ));
        }
    }

    public function logout()
    {
        try {
            $data = Auth::user()->currentAccessToken()->delete();
            if (!$data) {
                throw new Exception('Something goes wrong when logout your account');
            }
            return $data;
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->json(
                [
                    'success' => false,
                    'message' =>
                    $ex->getMessage(),
                ],
            ));
        }
    }

    /* New Password Service */
    public function forgotPassword($request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            # Check user exist
            if (!$user) {
                throw new Exception('User not found');
            }

            if ($user->status == 0) {
                throw new Exception('Your Account is suspended, please contact Admin.');
            }

            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status == Password::RESET_LINK_SENT) {
                $data = __($status);

                return $data;
            }


            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        } catch (Exception $ex) {
            throw new HttpResponseException(response()->json(
                [
                    'success' => false,
                    'message' =>
                    $ex->getMessage(),
                ],
            ));
        }
    }

    public function reset($request)
    {
        # Validate
        $request->validated($request->all());

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return true;
        }

        return false;
    }

    /* Confirm Password */
    public function confirm($request)
    {
        $user = Auth::user();

        if ($user->status == 0) {
            throw new Exception('Your Account is suspended, please contact Admin.');
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return false;
        }
        return true;
    }

    /* Change Password */
    public function changePassword($request)
    {
        $user = Auth::user();

        if ($user->status == 0) {
            throw new Exception('Your Account is suspended, please contact Admin.');
        }

        #Match The Old Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return false;
        }

        #Update the new Password
        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->newpassword)
        ]);

        return true;
    }

    /* User info */
}
