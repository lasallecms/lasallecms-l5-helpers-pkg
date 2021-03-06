<?php
namespace Lasallecms\Helpers\Dates;

/**
 *
 * Helpers package for the LaSalle Content Management System, based on the Laravel 5 Framework
 * Copyright (C) 2015 - 2016  The South LaSalle Trading Corporation
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
 * @copyright  (c) 2015 - 2016, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */


// Laravel facades
use Illuminate\Support\Facades\Config;

// Third party classes
use Carbon\Carbon;


/*
 * Date/time helper
 */
class DatesHelper
{
    /*
     * Convert date to string in Y-m-d H:i:s format
     *
     * @param  datetime $date
     * @return string
     */
    public static function convertDatetoFormattedDateString($date)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date, self::setTimeZone())
            ->toFormattedDateString();
    }

    /*
     * Convert date to string in Y-m-d format
     *
     * @param  datetime $date
     * @return string
     */
    public static function convertDateONLYtoFormattedDateString($date)
    {
        if (!$date) return "n/a";

        return Carbon::createFromFormat('Y-m-d', $date, self::setTimeZone())
            ->toFormattedDateString();
    }


    /*
     * Set today to yyyy-mm-dd format
     * I could not figure out how to return Y-m-d format with Carbon, so just using substr()
     *
     * @return string
     */
    public static function todaysDateNoTime()
    {
        self::setTimeZone();
        return substr(Carbon::today(),0,10);
    }


    /*
     * Today's date, using the time zone in the app's config/app.php.
     *
     * @return string
     */
    public static function todaysDateSetToLocalTime()
    {
        self::setTimeZone();
        return Carbon::today();
    }

    /*
     * Set the time zone. If there is no default time zone, set it to Toronto!
     *
     * @return void
     */
    public static function setTimeZone()
    {
        $timeZone = Config::get('app.timezone');

        if (
            ($timeZone == '')
             || (strtoupper($timeZone) == 'UTC')
             || (empty($timeZone))
        )
            $timeZone = 'America/Toronto';

        date_default_timezone_set($timeZone);

        return;
    }

}

