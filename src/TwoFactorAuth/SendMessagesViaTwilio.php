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

// Third party classes
use Aloha\Twilio\Twilio;

/**
 * Class SendMessagesViaTwilio
 * @package Lasallecms\Helpers\TwoFactorAuth
 */
class SendMessagesViaTwilio
{

    /**
     * Send text message via Twilio's API
     *
     * @param $phoneCountryCode
     * @param $phoneNumber
     * @param $message
     */
    public function sendSMS($phoneCountryCode, $phoneNumber, $message) {

        // concatenate to create the Twilio friendly phone number
        $phone  = $this->buildTwilioToPhoneNumber($phoneCountryCode, $phoneNumber);

        // Get the Twilio config settings
        $twilioConfigSettings = $this->getTwilioConfig();

        // Send the text message via Twilio's API
        $twilio = new \Aloha\Twilio\Twilio($twilioConfigSettings['sid'], $twilioConfigSettings['token'], $twilioConfigSettings['fromNumber']);
        $twilio->message($phone, $message);
    }



    /*********************************************************************************/
    /*                           TWILIO CONFIG                                       */
    /*********************************************************************************/

    /**
     * Get the Twilio config settings from the laravel-twilio package
     *
     * @param   text   $conection    The connection to fetch. Can have multiple Twilio connections in the config.
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
    /*                           TWILIO PHONE NUMBER                                 */
    /*********************************************************************************/
    /**
     * Create the phone number in the Twilio format
     *
     * @param $phoneCountryCode
     * @param $phoneNumber
     * @return string
     */
    public function buildTwilioToPhoneNumber($phoneCountryCode, $phoneNumber) {

        // concatenate to create the Twilio friendly phone number
        $phone  = "+";
        $phone .= $phoneCountryCode;
        $phone .= $phoneNumber;

        return $phone;
    }
}