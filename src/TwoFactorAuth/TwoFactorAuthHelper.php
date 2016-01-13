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

// Laravel facades
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

// Laravel classes
use Illuminate\Http\Request;

// Third party classes
use Aloha\Twilio\Twilio;
use Carbon\Carbon;

class TwoFactorAuthHelper
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * TwoFactorAuthHelper constructor.
     * @param \Illuminate\Http\Request  $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /*********************************************************************************/
    /*                            MAIN METHODS                                       */
    /*********************************************************************************/

    /**
     * @param  int    $userId          User ID
     * @return void
     */
    public function doTwoFactorAuthLogin($userId) {

        // Piece together the Twilio friendly phone number

        // (i) get the user's country code and phone number
        $userPhoneCountryCode = $this->getUserPhoneCountryCode($userId);
        $userPhoneNumber      = $this->getUserPhoneNumber($userId);

        // (ii) concatenate to create the Twilio friendly phone number
        $phoneNumber  = "+";
        $phoneNumber .= $userPhoneCountryCode;
        $phoneNumber .= $userPhoneNumber;


        // Get the Twilio config settings
        $twilioConfigSettings = $this->getTwilioConfig();

        // Get the 2FA code
        $codeToInput = $this->getCodeToInput();

        // Update the user's database record wtih the 2FA code & timestamp
        $this->updateUserRecordWithTwoFactorAuthCode($codeToInput, $userId);

        // Put together the SMS message
        $message  = config('lasallecmsfrontend.site_name');
        $message .= ". Your two factor authorization login code is ";
        $message .= $codeToInput;

        // Twilio is sms provider of choice right now...
        $twilio = new \Aloha\Twilio\Twilio($twilioConfigSettings['sid'], $twilioConfigSettings['token'], $twilioConfigSettings['fromNumber']);
        $twilio->message($phoneNumber, $message);
    }

    /**
     * Has too much time passed between issuing the 2FA code and this code being
     * entered into the verification form?
     *
     * @param  int    $userId          User ID
     * @return bool
     */
    public function isTwoFactorAuthFormTimeout($userId) {

        $startTime = strtotime($this->getUserSmsTokenCreatedAt($userId));
        $now       = strtotime(Carbon::now());

        // The time difference is in seconds, we want in minutes
        $timeDiff = ($now - $startTime)/60;

        $minutes2faFormIsLive = config('auth.auth_2fa_minutes_smscode_is_live');

        if ($timeDiff > $minutes2faFormIsLive) {

            // clear out the user's 2FA sms code and timestamp
            $this->clearUserTwoFactorAuthFields($userId);

            // clear the user_id session variable
            $this->clearUserIdSessionVar();

            return true;
        }

        return false;
    }

    /**
     * Did the user input the correct 2FA code?
     *
     * @param  int    $userId          User ID
     * @return bool
     */
    public function isInputtedTwoFactorAuthCodeCorrect($userId) {
        $inputted2faCode = $this->request->input('2facode');

        $sent2faCode     = $this->getUserSmsToken($userId);

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
            ->update(['sms_token' => null, 'sms_token_created_at' => null] )
        ;
    }



    /*********************************************************************************/
    /*                           TWILIO CONFIG                                       */
    /*********************************************************************************/

    /**
     * Get the Twilio config settings from the laravel-twilio package
     *
     * @param  text  $conection    The connection to fetch. Can have multiple Twilio connections in the config.
     * @return  array
     */
    public function getTwilioConfig($connection='twilio')
    {
        $twilioconfig   = config('twilio.twilio');

        $configSettings = [];

        $configSettings['sid']        = $twilioconfig['connections'][$connection]['sid'];
        $configSettings['token']      = $twilioconfig['connections'][$connection]['token'];
        $configSettings['fromNumber'] = $twilioconfig['connections'][$connection]['from'];

        return $configSettings;
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


