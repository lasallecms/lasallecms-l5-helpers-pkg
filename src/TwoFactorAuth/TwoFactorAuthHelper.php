<?php

namespace Lasallecms\Helpers\TwoFactorAuth;

/**
 *
 * Helpers package for the LaSalle Content Management System, based on the Laravel 5 Framework
 * Copyright (C) 2015  The South LaSalle Trading Corporation
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @package    Helpers package for the LaSalle Content Management System
 * @link       http://LaSalleCMS.com
 * @copyright  (c) 2015, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */

// LaSalle Software
use Lasallecms\Helpers\TwoFactorAuth\SendMessagesViaTwilio;

// Laravel facades
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

// Laravel classes
use Illuminate\Http\Request;

// Third party classes
use Carbon\Carbon;

class TwoFactorAuthHelper
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var Lasallecms\Helpers\TwoFactorAuth\SendMessagesViaTwilio
     */
    protected $sendMessagesViaTwilio;

    /**
     * TwoFactorAuthHelper constructor.
     * @param \Illuminate\Http\Request  $request
     * @param Lasallecms\Helpers\TwoFactorAuth\SendMessagesViaTwilio $twilio
     */
    public function __construct(Request $request, SendMessagesViaTwilio $sendMessagesViaTwilio)
    {
        $this->request = $request;
        $this->sendSMS = $sendMessagesViaTwilio;
    }

    /*********************************************************************************/
    /*                            MAIN METHODS                                       */
    /*********************************************************************************/

    /**
     * Two Factor Authorization for the front-end LOGIN
     *
     * @param  int    $userId          User ID
     * @return void
     */
    public function doTwoFactorAuthLogin($userId) {

        // Get the 2FA code
        $codeToInput = $this->getCodeToInput();

        // Update the user's database record wtih the 2FA code & timestamp
        $this->updateUserRecordWithTwoFactorAuthCode($codeToInput, $userId);

        // Put together the SMS message
        $message  = config('lasallecmsfrontend.site_name');
        $message .= ". Your two factor authorization login code is ";
        $message .= $codeToInput;

        $this->sendSMS->sendSMS($this->getUserPhoneCountryCode($userId), $this->getUserPhoneNumber($userId), $message);
    }

    /**
     * Two Factor Authorization for the front-end REGISTRATION
     *
     * @param  array    $data
     * @return void
     */
    public function doTwoFactorAuthRegistration($data) {

        // Get the 2FA code
        $codeToInput = $this->getCodeToInput();

        // Set this 2FA code into a session variable, since
        // this is the only opportunity to so save the ephemerally generated 2FA
        $this->request->session()->put('codeToInput', $codeToInput);


        // Put together the SMS message
        $message  = config('lasallecmsfrontend.site_name');
        $message .= ". Your two factor authorization login code is ";
        $message .= $codeToInput;


        $this->sendSMS->sendSMS($data['phone_country_code'], $data['phone_number'], $message);
    }



    /**
        echo "<hr>timeDiff = ".$timeDiff;
        echo "<br>minutes2faFormIsLive = ".$minutes2faFormIsLive;
     * Has too much time passed between issuing the 2FA code and this code being
     * entered into the verification form?
     *
     * @param  int       $userId          User ID --> if null, then called by 2FA registration
     * @param  datetime  $startTime       Time 2FA form created --> if null, then called by 2FA login
     * @return bool
     */
    public function isTwoFactorAuthFormTimeout($userId=null, $startTime=null ) {

        if (isset($userId)) {

            // User wants to login, performing 2FA for this login
            $startTime = strtotime($this->getUserSmsTokenCreatedAt($userId));
        } else {

            // New front-end registration, performing 2FA for this registration
            $startTime = strtotime($startTime);
        }

        $now       = strtotime(Carbon::now());

        // The time difference is in seconds, we want in minutes
        $timeDiff = ($now - $startTime)/60;

        $minutes2faFormIsLive = config('auth.auth_2fa_minutes_smscode_is_live');

        if ($timeDiff > $minutes2faFormIsLive) {

            if (isset($userId)) {
                // clear out the user's 2FA sms code and timestamp
                $this->clearUserTwoFactorAuthFields($userId);

                // clear the user_id session variable
                $this->clearUserIdSessionVar();
            } else {
                $this->clearTwoFactorAuthCodeToInput();
            }

            return true;
        }

        return false;
    }

    /**
     * Did the user input the correct 2FA code?
     *
     * @param  int    $userId                      User ID --> if null, then called by 2FA registration;
     *                                             otherwise, called by 2FA login

     * @return bool
     */
    public function isInputtedTwoFactorAuthCodeCorrect($userId=null) {
        $inputted2faCode = $this->request->input('2facode');

        if (isset($userId)) {
            $sent2faCode     = $this->getUserSmsToken($userId);
        } else {
            $sent2faCode     = $this->request->session()->get('codeToInput');
        }


        if ($inputted2faCode == $sent2faCode) {
            return true;
        }

        return false;
    }



    /*********************************************************************************/
    /*                         CONFIG SETTINGS                                       */
    /*********************************************************************************/

    /**
     * Is the config/auth 2FA FRONT-END REGISTRATION setting enabled?
     *
     * @return bool
     */
    public function isAuthConfigEnableTwoFactorAuthRegistration() {
        if (config('auth.auth_enable_two_factor_authorization_frontend_registration')) {
            return true;
        }
        return false;
    }

    /**
     * Is the config/auth 2FA FRONT-END LOGIN setting enabled?
     *
     * @return bool
     */
    public function isAuthConfigEnableTwoFactorAuthLogin() {
        if (config('auth.auth_enable_two_factor_authorization_frontend_login')) {
            return true;
        }
        return false;
    }

    /**
     * Is the config/auth 2FA ADMIN LOGIN setting enabled?
     *
     * @return bool
     */
    public function isAuthConfigEnableTwoFactorAuthAdminLogin() {
        if (config('auth.auth_enable_two_factor_authorization_admin_login')) {
            return true;
        }
        return false;
    }



    /*********************************************************************************/
    /*                         USER 2FA SETTINGS                                     */
    /*********************************************************************************/

    /**
     * Is user enabled for Two Factor Authorization
     *
     * @param  int    $userId          User ID
     * @return bool
     */
    public function isUserTwoFactorAuthEnabled($userId) {
        $result = DB::table('users')
            ->where('id', '=', $userId)
            ->value('two_factor_auth_enabled')
        ;

        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * Does the user have a country code and phone number for 2FA?
     *
     * @param  int    $userId          User ID
     */
    public function existstUserCountryCodeAndPhoneNumber($userId) {
        $countryCode = DB::table('users')
            ->where('id', '=', $userId)
            ->value('phone_country_code')
        ;

        $phoneNumber =  DB::table('users')
            ->where('id', '=', $userId)
            ->value('phone_number')
        ;

        if ((!$countryCode) || (!$phoneNumber)) {
            return false;
        }

        return true;
    }

    /**
     * The user's phone's country code
     *
     * @param  int    $userId          User ID
     * @return string
     */
    public function getUserPhoneCountryCode($userId) {
        return DB::table('users')
            ->where('id', '=', $userId)
            ->value('phone_country_code')
            ;
    }

    /**
     * The user's phone number
     *
     * @param  int    $userId          User ID
     */
    public function getUserPhoneNumber($userId) {
        return DB::table('users')
            ->where('id', '=', $userId)
            ->value('phone_number')
            ;
    }

    /**
     * The user's sms_token
     *
     * @param  int    $userId          User ID
     */
    public function getUserSmsToken($userId) {
        return DB::table('users')
            ->where('id', '=', $userId)
            ->value('sms_token')
            ;
    }

    /**
     * The user's sms_token_created_at
     *
     * @param  int    $userId          User ID
     */
    public function getUserSmsTokenCreatedAt($userId) {
        return DB::table('users')
            ->where('id', '=', $userId)
            ->value('sms_token_created_at')
            ;
    }

    /**
     * UPDATE the user record for fields "sms_token" and "sms_token_created_at"
     *
     * @param  text   $codeToInput     The code sent to the user via sms that has to be entered into a form to allow login
     * @param  int    $userId          User ID
     * @return void
     */
    public function updateUserRecordWithTwoFactorAuthCode($codeToInput, $userId) {

        $now = Carbon::now();

        DB::table('users')
            ->where('id', $userId)
            ->update(['sms_token' => $codeToInput, 'sms_token_created_at' => $now] )
        ;
    }

    /**
     * Clear the user record for fields "sms_token" and "sms_token_created_at"
     *
     * @param  int    $userId          User ID
     * @return void
     */
    public function clearUserTwoFactorAuthFields($userId) {
            DB::table('users')
                ->where('id', $userId)
                ->update(['sms_token' => null, 'sms_token_created_at' => null]);

    }


    public function clearTwoFactorAuthCodeToInput() {
        return $this->request->session()->remove('CodeToInput');
    }



    /*********************************************************************************/
    /*                      UPDATE THE USER's LAST LOGIN DB FIELDS                   */
    /*********************************************************************************/

    /**
     * UPDATE the user record for fields "last_login" and "last_login_ip"
     *
     * This method is here because it is convenient, as this class is already injected in the auth classes
     *
     * @param  int    $userId          User ID
     * @return void
     */
    public function updateUserRecordWithLastlogin($userId) {

        $now = Carbon::now();
        $ip  = $this->request->getClientIp();

        DB::table('users')
            ->where('id', $userId)
            ->update(['last_login' => $now, 'last_login_ip' => $ip] )
        ;
    }



    /*********************************************************************************/
    /*                         OTHER 2FA HELPER METHODS                              */
    /*********************************************************************************/

    /**
     * Set the 'user_id' session variable to the $userID
     *
     * @param  int                       $userId          User ID
     * @return mixed
     */
    public function setUserIdSessionVar($userId) {
        return $this->request->session()->put('user_id', $userId);
    }

    /**
     * Clear the 'user_id' session variable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function clearUserIdSessionVar() {
        return $this->request->session()->remove('user_id');
    }

    /**
     * The code to send to the user via sms that they then must enter in order to login
     *
     * @return int
     */
    public function getCodeToInput() {

        // 7 digit random number
        $min = 1000000;
        $max = 9999999;

        return rand($min, $max);
    }

    /**
     * Upon successful front-end login, redirect to this path
     *
     * @return string
     */
    public function redirectPathUponSuccessfulFrontendLogin()
    {
        if (property_exists($this, 'redirectPath')) {
            return $this->redirectPath;
        }

        if (property_exists($this, 'redirectPath')) {
            return $this->redirectTo;
        }

        if (config('lasasllecmsfrontend.frontend_redirect_to_this_view_when_user_successfully_logged_in_to_front_end') != '') {
            return config('lasasllecmsfrontend.frontend_redirect_to_this_view_when_user_successfully_logged_in_to_front_end');
        }

        return '/home';
    }
}


