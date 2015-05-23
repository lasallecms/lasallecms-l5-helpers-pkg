<?php
namespace Lasallecms\Helpers\HTML;

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

// Laravel facades
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/*
 * HTML helpers
 */
class HTMLHelper
{
    ///////////////////////////////////////////////////////////////////
    ///////////////////    GENERAL HTML HELPERS    ////////////////////
    ///////////////////////////////////////////////////////////////////

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

    /*
     * Format a db result set where an element is displayed on a separate line.
     * Eg: A nice little list of categories for each post_id, with
     *     each category on a separate line,
     *
     * @param  array  $results
     * @return text
     */
    public static function listSingleCollectionElementOnSeparateRow($results, $element = "title")
    {
        if (count($results) <1 ) return '<span class="text-danger">n/a</span>';

        $html = "";
        $i = 1;
        foreach ($results as $result)
        {
            if ($i == 1)
            {
                $html .= $result->$element;
            } else {
                $html .= "<br />".$result->$element;
            }
            $i++;
        }

        return $html;
    }

    /*
     * Grab the title by ID
     *
     *
     * @param    string     $table
     * @param    int        $id
     * @return   string
     */
    public static function getTitleById($table, $id)
    {
        return DB::table($table)->where('id', '=', $id)->pluck('title');
    }




    ///////////////////////////////////////////////////////////////////
    ///////////         CATEGORY ADMIN HELPERS             ////////////
    ///////////    AS CATEGORY HAS ITSELF AS PARENT_ID     ////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Display the parent category
     *
     * @param  int         $parent_id
     * @param  eloquent    $categoryRepository   The category repository
     * @return string
     */
    public static function displayParentCategoryTitle($parent_id, $categoryRepository)
    {
        if ($parent_id == 0) return "";

        return $categoryRepository->getFind($parent_id)->title;
    }

    /*
     * Create a dropdown with a single select for the parent category
     *
     * @param  collection  $categories  Laravel collection object of category
     * @return string
     */
    public static function categoryParentSingleSelectCreate($categories)
    {
        // STEP 1: Initiatize the html select tag
        $html = "";
        $html .= '<select name="parent_id" id="parent_id" size="6" class="form-control" >';

        $html .= '<option ';
        $html .= 'value="';
        $html .= 0;
        $html .= '">';
        $html .= 'No Parent Category';
        $html .= '</option>"';

        // STEP 2: Construct the <option></option> categories for ALL categories in the categories table
        foreach ($categories as $category) {
            $html .= '<option ';
            $html .= 'value="';
            $html .= $category->id;
            $html .= '">';
            $html .= $category->title;
            $html .= '</option>"';
        }
        $html .= '</select>';

        return $html;
    }

    /*
     * Create a multiple select drop down for tags
     * that have the existing tags for that post already selected
     *
     * There is only one parent_id per category
     *
     * @param  collection       $categories        All categories
     * @param  int              $parent id         category's parent id
     * @param  int              $category id       category's id
     * @return string
     */
    public static function categoryParentSingleSelectEdit($categories, $parent_id, $category_id)
    {
        // STEP 1: Initiatize the html select tag
        $html = "";
        $html .=  '<select name="parent_id" id="parent_id" size="6" class="form-control">';

        $html .= '<option ';

        if ( $parent_id == 0)  $html .= ' selected="selected" ';

        $html .= 'value="';
        $html .= 0;
        $html .= '">';
        $html .= 'No Parent Category';
        $html .= '</option>"';

        // STEP 2: Construct the <option></option> categories for ALL categories in the categories table
        foreach ($categories as $category)
        {
            if ($category->id == $category_id) continue;


            // If this tag is attached to the post, then SELECTED it
            if ( $category->id == $parent_id )
            {
                $selected = ' selected="selected" ';
            } else {
                $selected = "";
            }
            $html .= '<option ';
            $html .= $selected;
            $html .= 'value="';
            $html .= $category->id;
            $html .= '">';
            $html .= $category->title;
            $html .= '</option>"';
        }
        $html .= '</select>';

        return $html;
    }



    ///////////////////////////////////////////////////////////////////
    /////////////////     ADMIN TITLE HELPERS       ///////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Page title for admin pages
     *
     * @param  string           $package_title       package's title
     * @param  string           $table_type_plural   table's type, in the plural
     * @param  string           $extra_title         extra text to append to the table's title
     * @return string
     */
    public static function adminPageTitle($package_title, $table_type_plural, $extra_title='')
    {
        $html  = '';
        $html .= '<br /><br />';
        $html .= '<div class="row">';
        $html .= '    <div class="oaerror info">';
        $html .= '        <strong>'.$package_title.'</strong> - '.$table_type_plural;
        $html .= ' '.$extra_title;
        $html .= '    </div';
        $html .= '<br /><br />';
        $html .= '</div>';
        $html .= '<br /><br />';

        return $html;
    }

    public static function adminPageSubTitle($record = null, $modelClass)
    {
        $html = '';
        $html .= '<div class="row">';
        $html .= '<div class="col-md-3"></div>';
        $html .= '<div class="col-md-6">';
        $html .= '<h1>';
        $html .= '<span class="label label-info">';

        if ($record)
        {
            $html .= 'Edit the ';
            $html .= $modelClass;
            $html .= ': "';
            $html .= $record->title;
            $html .= '"';
        } else {
            $html .= 'Create a ';
            $html .= $modelClass;
        }
        $html .= '</span>';
        $html .= '</h1>';
        $html .= '</div>';
        $html .= '<div class="col-md-3"></div>';
        $html .= '</div>';

        return $html;
    }



    ///////////////////////////////////////////////////////////////////
    /////////////     ADMIN FORM FIELD HELPERS       //////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Transform the field name into a format suitable for a form label
     *
     * @param  string  $name   Form field name
     * @return string
     */
    public static function adminFormFieldLabel($name)
    {
        if ($name == "id") return "ID";

        $html = str_replace("_", " ", $name);
        $html = ucwords($html);
        return $html;
    }

    /*
     * Create button for admin pages
     *
     * @param  string           $resource_route_name        resource route's name
     * @param  string           $table_type_singular        table's type, in the singular
     * @param  string           $pull                       bootstrap "pull" left or right
     * @return string
     */
    public static function adminCreateButton($resource_route_name, $table_type_singular, $pull="right")
    {
        $full_url = route('admin.'.$resource_route_name.'.create');

        $html  = '';
        $html .= '<a class="btn btn-default pull-'.$pull.'"';
        $html .= ' href="';
        $html .= $full_url;
        $html .= '" role="button">';
        $html .= '<span class="glyphicon glyphicon-heart-empty"></span>  Create '.$table_type_singular;
        $html .= '</a><br /><br /><br />';
        return $html;
    }

    /*
     * Create button for admin pages
     *
     * @param  string           $resource_route_name        resource route's name
     * @param  string           $message                    optional button text
     * @param  string           $pull                       bootstrap "pull" left or right
     * @return string
     */
    public static function adminIndexButton($resource_route_name, $message=null, $pull="right")
    {
        $full_url = route('admin.'.$resource_route_name.'.index');

        if (!$message) $message = $resource_route_name;

        $html  = '';
        $html .= '<a class="btn btn-default pull-'.$pull.'"';
        $html .= ' href="';
        $html .= $full_url;
        $html .= '" role="button">';
        $html .= '<span class="glyphicon glyphicon-heart-empty"></span>  '.$message;
        $html .= '</a><br /><br /><br />';
        return $html;
    }
}

