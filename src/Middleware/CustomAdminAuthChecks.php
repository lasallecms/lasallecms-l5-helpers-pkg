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

// LaSalle Software
use Lasallecms\Usermanagement\Http\Middleware\Admin\CustomAdminAuthChecks as CustomChecksFromUserMgmtPkg;

// Laravel facades
use Illuminate\Support\Facades\Auth;

// Laravel classes
use Illuminate\Http\Request;
use Closure;

/*
 * Perform custom login checks.
 *
 * Note that these custom checks originate in the User Management package, not in this Admin package.
 */
class CustomAdminAuthChecks
{


    public function __construct(CustomChecksFromUserMgmtPkg $customChecksFromUserMgmtPkg) 
    {
        $this->customChecksFromUserMgmtPkg = $customChecksFromUserMgmtPkg;
    }


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) 
    {

        // Allowed IP Addresses
        if ($this->customChecksFromUserMgmtPkg->isAllowedIPAddressesCheck()) {
            if (!$this->customChecksFromUserMgmtPkg->ipAddressCheck( $this->customChecksFromUserMgmtPkg->getAllowedIPAddresses(), $this->customChecksFromUserMgmtPkg->getRequestIPAddress($request)) ) {

                Auth::logout();

                return redirect('admin/login')
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => 'You are not authorized for that operation.',
                    ]);
            }
        }

        // User must be enabled
        // This test is mandatory! So, no setting in the config
        if (!$this->UserEnabledCheck() )
        {

            Auth::logout();

            return redirect('admin/login')
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'You are not authorized to login to the admin.',
                ]);
        }

        // User must be activated
        // This test is mandatory! So, no setting in the config
        if (!$this->UserActivatedCheck() )
        {

            Auth::logout();

            return redirect('admin/login')
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'You are not activated to login to the admin.',
                ]);
        }

        // Allowed users
        if ($this->customChecksFromUserMgmtPkg->isAllowedUsersCheck())
        {
            if (!$this->customChecksFromUserMgmtPkg->allowedUsersCheck($this->customChecksFromUserMgmtPkg->getAllowedUsers(), $this->getAuthEmail() ) )
            {

                Auth::logout();

                return redirect('admin/login')
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => 'You are not authorized for that operation.',
                    ]);
            }
        }

        // Allowed user groups
        if ($this->customChecksFromUserMgmtPkg->isUserGroupCheck())
        {
            if (!$this->customChecksFromUserMgmtPkg->allowedUserGroupCheck($this->customChecksFromUserMgmtPkg->getAllowedUserGroups(), $this->getAuthUserGroup()) )
            {
                Auth::logout();

                return redirect('admin/login')
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => 'You are not authorized for that operation.',
                    ]);
            }
        }

        return $next($request);
    }


    /*
     * Get the email of the logged in user.
     *
     * @return string
     */
    public function getAuthEmail()
    {
        $emailRequest = Auth::user()->email;
        return $emailRequest;
    }

    /*
     * Get the user group of the logged in user
     *
     * @return string
     */
    public function getAuthUserGroup()
    {
        $user_groups = Auth::user()->find(Auth::user()->id)->group;

        // create an array
        $usergroupArray = [];
        foreach ($user_groups as $user_group) {
            $usergroupArray[] = $user_group->title;
        }

        return $usergroupArray;
    }

    /*
     * Is the user enabled?
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function UserEnabledCheck()
    {
        $user = Auth::user()->where('email', '=', Auth::user()->email )->get()->first();

        if ( $user ) {
            return $user->enabled;
        } else {
            return false;
        }
    }

    /*
     * Is the user activated?
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function UserActivatedCheck()
    {
        $user = Auth::user()->where('email', '=', Auth::user()->email )->get()->first();

        if ( $user ) {
            return $user->activated;
        } else {
            return false;
        }
    }
}