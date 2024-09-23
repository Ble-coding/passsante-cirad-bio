<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use Auth;
use Mail;
use App\Mail\ForgotPasswordMail;
use Carbon\Carbon;
use DB;
use Str;

class AuthController extends AppBaseController
{
    public function login(Request $request): JsonResponse
    {
        $email = $request->get('email');
        $password = $request->get('password');

        if (empty($email) or empty($password)) {
            return $this->sendError(__('messages.common.username_and_password_required'), 422);
        }
        $user = User::whereRaw('lower(email) = ?', [$email])->first();

        if($user->email_verified_at == null){
            return $this->sendError('Please verify your email', 422);
        }
        
        if (empty($user)) {
            return $this->sendError(__('messages.common.invalid_username_password'), 422);
        }

        if (! Hash::check($password, $user->password)) {
            return $this->sendError(__('messages.common.invalid_username_password'), 422);
        }

        $token = $user->createToken('token')->plainTextToken;
        $user->last_name = $user->last_name ?? '';

        if ($user->hasRole('Doctor')) {
            $data = [
                'token' => $token,
                'role' => 'Doctor',
                'user' => $user->prepareData(),
            ];
        }   elseif ($user->hasRole('Patient')) {
            $data = [
                'token' => $token,
                'role' => 'Patient',
                'user' => $user->prepareData(),
            ];
        } elseif ($user->hasRole('Super Admin')){
            $data = [
                'token' => $token,
                'role' => 'Super Admin',
                'user' => $user->prepareData(),
            ];
        } elseif ($user->hasRole('Admin')){
            $data = [
                'token' => $token,
                'role' => 'Admin',
                'user' => $user->prepareData(),
            ];
        } else {

            return $this->sendError(__('messages.common.invalid_username_password'), 422);
        }

        return $this->sendResponse($data, __('messages.common.logged_in_successfully'));
    }

    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->where('id', Auth::user()->currentAccessToken()->id)->delete();

        return $this->sendSuccess(__('messages.common.logout_successfully'));
    }

    /**
     * @throws ValidationException
     */
    public function sendPasswordResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $data['user'] = User::whereEmail($request->email)->first();

        if (! $data['user']) {
            return $this->sendError(__('messages.common.we_cant_find_user'));
        }

        $data['token'] = encrypt($data['user']->email.' '.$data['user']->id);

        $data['link'] = 'https://infyhmsflutter.page.link/?link=http://com.example.infyhms_flutter/'.$data['token'].'&apn=com.example.infyhms_flutter';

        Mail::to($data['user']->email)
            ->send(new ForgotPasswordMail('emails.forgot_password',
                'Reset Password Notification',
                $data));

        $user = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if ($user) {
            DB::table('password_reset_tokens')->where('email', $user->email)->update([
                'email' => $request->email,
                'token' => $data['token'],
                'created_at' => Carbon::now(),
            ]);
        } else {
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $data['token'],
                'created_at' => Carbon::now(),
            ]);
        }

        return $this->sendSuccess(__('messages.common.we_have_your_password_resetk_link'));
    }

    /**
     * @throws ValidationException
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $decrypttoken = decrypt($request->token);
        $getEmail =  explode(' ', $decrypttoken);

        $request->validate([
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $tokenData = DB::table('password_reset_tokens')
            ->where('token', $request->token)->first();

        if (! $tokenData) {
            return $this->sendError(__('messages.common.this_password_reset_token_is_invalid'));
        }

        $user = User::where('email', $getEmail[0])->first();
        if (! $user) {
            return $this->sendError(__('messages.common.we_cant_find_user'));
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')
            ->where('token', $request->token)->delete();

        return $this->sendSuccess(__('messages.common.password_reset_successfully'));

    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! Hash::check($request->old_password, $user->password)) {
            return $this->sendError(__('messages.common.please_enter_correct_old_password'));
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return $this->sendSuccess(__('messages.common.password_updated'));
    }


}
