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


    ///////////////////////////////////////////////////////////////////
    /////////////////      POST ADMIN HELPERS      ////////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Create a dropdown with multiple selects for the categories
     *
     * @param  collection  $categories  Laravel collection object of categories
     * @return string
     */
    public static function postCategoryMultipleSelectCreate($categories)
    {
        // STEP 1: Initiatize the html select tag
        $html = "";
        $html .= '<select name="categories[]" id="categories" size="6" class="form-control" multiple>';

        // STEP 2: Construct the <option></option> tags for ALL categories in the categories table
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
     * Create a multiple select drop down for categories
     * that have the existing categories for that post already selected
     *
     * @param  collection      $categories              All categories
     * @param  int             $id                      Post ID
     * @param  collection      $postAllCategoriesById   All catgories associated with this post
     * @return string
     */
    public static function postCategoryMultipleSelectEdit($categories, $id, $postAllCategoriesById)
    {
        // STEP 1: Create an array of tag IDs that are currently attached to the post
        $categories_attached_to_this_post = array();

        foreach ($postAllCategoriesById as $category)
        {
            $categories_attached_to_this_post[] = $category->id;
        }

        // STEP 2: Initiatize the html select category
        $html = "";
        $html .=  '<select name="categories[]" id="tags" size="6" class="form-control" multiple>';

        // STEP 3: Construct the <option></option> tags for ALL tags in the tags table
        foreach ($categories as $category)
        {
            // If this tag is attached to the post, then SELECTED it
            if ( in_array($category->id, $categories_attached_to_this_post) )
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



    /*
     * Create a dropdown with multiple selects for the tags
     *
     * @param  collection  $tags  Laravel collection object of tags
     * @return string
     */
    public static function postTagMultipleSelectCreate($tags)
    {
        // STEP 1: Initiatize the html select tag
        $html = "";
        $html .= '<select name="tags[]" id="tags" size="6" class="form-control" multiple>';

        // STEP 2: Construct the <option></option> tags for ALL tags in the tags table
        foreach ($tags as $tag) {
            $html .= '<option ';
            $html .= 'value="';
            $html .= $tag->id;
            $html .= '">';
            $html .= $tag->title;
            $html .= '</option>"';
        }
        $html .= '</select>';

        return $html;
    }

    /*
     * Create a multiple select drop down for tags
     * that have the existing tags for that post already selected
     *
     * @param  collection      $tags              All tags
     * @param  int             $id                Post ID
     * @param  collection      $postAllTagsById   All tags associated with this post
     * @return string
     */
    public static function postTagMultipleSelectEdit($tags, $id, $postAllTagsById)
    {
        // STEP 1: Create an array of tag IDs that are currently attached to the post
        $tags_attached_to_this_post = array();

        foreach ($postAllTagsById as $tag)
        {
            $tags_attached_to_this_post[] = $tag->id;
        }

        // STEP 2: Initiatize the html select tag
        $html = "";        //foreach ( Post::find($id)->tags as $ttgg)
        $html .=  '<select name="tags[]" id="tags" size="6" class="form-control" multiple>';

        // STEP 3: Construct the <option></option> tags for ALL tags in the tags table
        foreach ($tags as $tag)
        {
            // If this tag is attached to the post, then SELECTED it
            if ( in_array($tag->id, $tags_attached_to_this_post) )
            {
                $selected = ' selected="selected" ';
            } else {
                $selected = "";
            }
            $html .= '<option ';
            $html .= $selected;
            $html .= 'value="';
            $html .= $tag->id;
            $html .= '">';
            $html .= $tag->title;
            $html .= '</option>"';
        }
        $html .= '</select>';
        return $html;
    }


    ///////////////////////////////////////////////////////////////////
    /////////////////    CATEGORY ADMIN HELPERS     ///////////////////
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




}

