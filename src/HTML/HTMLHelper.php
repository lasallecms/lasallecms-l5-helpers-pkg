<?php namespace Lasallecms\Helpers\HTML;

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
 * @version    1.0.0
 * @link       http://LaSalleCMS.com
 * @copyright  (c) 2015, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *s
 */

use URL;

/*
 * HTML helpers
 */
class HTMLHelper {

    /*
     * Convert yes/no to "check" or "remove" bootstrap buttons
     *
     * @param  mixed  $boolean-ish  Either "yes/no", 0/1, "true/false", true/false
     * @return string
     */
    public static function convertToCheckOrXBootstrapButtons($booleanIsh)
    {
        if (
            (strtolower($booleanIsh == "yes"))
            || ($booleanIsh == 1)
            || (strtolower($booleanIsh == "true"))
            || ($booleanIsh == true)
        ) {
            $html = '<i style="color: green;" class="fa fa-check fa-lg"></i>';
        } else {
            $html = '<i style="color: red;" class="fa fa-remove fa-lg"></i>';
        }
        return $html;
    }


    /*
     * Insert a bootstrap button to go back to the previous page.
     * Usage in your view: {!! back_button('Cancel') !!}
     *
     * @param  string  $body  Text that will display in the button
     * @return string
     */
    public static function back_button($body = 'Go Back')
    {
        $html ='<a class="btn btn-default btn-xs" href="';
        $html .= URL::previous();
        $html .= '" ';
        $html .= 'role="button">';
        $html .= '<span class="glyphicon glyphicon-remove"></span>';
        $html .= $body;
        $html .= '</a>';
        return $html;
    }


    /*
     * Insert a link to go back to the previous page.
     * Usage in your view: {!! link_back('Cancel') !!}
     *
     * @param  string  $body  Text that will display in the link
     * @return string
     */
    public static function back_link($body = 'Go Back')
    {
        return link_to(URL::previous(), $body);
    }


 }

