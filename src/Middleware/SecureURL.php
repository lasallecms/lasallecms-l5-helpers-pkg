<?php

namespace Lasallecms\Helpers\Middleware;

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
 * but WITHOUT ANY WARRANTY/var/www/html/lasallecms-l5-packages/packages/lasallecrm/lasallecrmadmin; without even the implied warranty of
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

// Laravel classes
use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;


/**
 * SecureURL
 *
 * Redirects any non-secure requests to their secure counterparts.
 *
 * (https://gist.github.com/nblackburn/a66e8e93561e277996aa)
 *
 * @param request      The request object.
 * @param $next        The next closure.
 * @return redirect    Redirs to the secure counterpart of the requested uri.
 */
class SecureURL
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //dd("SecureURL and config = ".Config::get('lasallecmsfrontend.secureURL'));

        if ( (Config::get('lasallecmsfrontend.secureURL')) && (!$request->secure()) ) {
           // return redirect()->secure($request->getRequestUri());
            return redirect("https://{$_SERVER['HTTP_HOST']}" . $request->getRequestUri());
        }

        return $next($request);
    }
}