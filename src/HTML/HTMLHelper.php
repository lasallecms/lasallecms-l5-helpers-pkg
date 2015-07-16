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

// LaSalle Software
use Lasallecms\Helpers\Images\ImagesHelper;

// Laravel facades
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/*
 * HTML helpers
 */
class HTMLHelper
{
    ///////////////////////////////////////////////////////////////////
    ///////////////////    MAINLY FOR INDEX LISTING    ////////////////
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

        //return "table = ".$table." and id = ".$id;

        // Special handling for users
        if ($table == "users")
        {
            return self::getUserForIndexListing($id);
        }

        // Special handling for posts (for post updates listing)
        if ($table == "posts")
        {
            return self::getPostForIndexListing($id);
        }

        $title = DB::table($table)->where('id', '=', $id)->pluck('title');

        if ($title == "") return '<span class="text-danger">n/a</span>';


        // ASSIGN COLOURS TO lookup_todo_priority_types
        if ($table == "lookup_todo_priority_types")
        {
            if (strtolower($title) == "green")
            {
                //return '<button type="button" class="btn btn-success btn-sm">'.$title.'</button>';
            }

            if (strtolower($title) == "yellow")
            {
                return '<button type="button" class="btn btn-warning btn-sm">'.$title.'</button>';
            }

            if (strtolower($title) == "red")
            {
                return '<button type="button" class="btn btn-danger btn-sm">'.$title.'</button>';
            }

        }

        return $title;
    }

    /*
     * Grab the User info from the "Users" table.
     * Created specifically for the CRM "People" index listing, but
     * displays in the post update create/edit form.
     *
     * @param    int        $id
     * @return   string
     */
    public static function getUserForIndexListing($id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        // A customer in LaSalleCRM does *NOT* have to be a LaSalle Software user
        if (empty($user->id))
        {
            return "n/a";
        }

        $html  = '<a href="';
        $html .= URL::route('admin.users.edit', $id);
        $html .= '">'.$user->name.'</a>';

        return $html;
    }

    /*
     * Grab the Post info from the "posts" table.
     * Created specifically for the "Post Updates" index listing
     *
     * @param    int        $id
     * @return   string
     */
    public static function getPostForIndexListing($id)
    {
        // Actually, it's dangerous and confusing to have the link to the post's EDIT
        // form display in the post update's create/edit form. So, I am omitting
        // the link; and, instead, am displaying the post's title with the post's
        // ID in brackets.

        /*
        $post = DB::table('posts')->where('id', $id)->first();
        $html  = '<a href="';
        $html .= URL::route('admin.posts.edit', $id);
        $html .= '" target="_blank">'.$post->title.'</a>';
        */

        $title = DB::table('posts')->where('id', '=', $id)->pluck('title');
        $html = $title." (".$id.")";

        return $html;
    }


    /*
     * Need a helper method to finagle the varchar field in the index listing.
     *
     * @param   array   $field
     * @param   string  $data
     * @return  string
     */
    public static function finagleVarcharFieldTypeForIndexListing($field, $data)
    {
        if (strtolower($field['persist_wash']) == "url")
        {
            $prefacedURL = self::prefaceURLwithHTTP($data);
            return '<a href="'.$prefacedURL.'" target="_blank">'.$data.'</a>';
        }

        return $data;
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
        if ($parent_id == 0) return "n/a";

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
        $table_type_plural = self::properPlural($table_type_plural);

        $html  = '';
        $html .= '<br /><br />';
        $html .= '<div class="row">';
        $html .= '    <div class="oaerror info">';
        $html .= '        <strong>'.$package_title.'</strong> - '.ucwords($table_type_plural);
        $html .= ' '.$extra_title;
        $html .= '    </div';
        $html .= '<br /><br />';
        $html .= '</div>';
        $html .= '<br /><br />';

        return $html;
    }

    /*
     * Display button with a label describing what the form is doing.
     *
     * @param  object           $record       Optional record from the database
     * @param  string           $modelClass   The model's classname
     * @param  bool             $show         True when from controller SHOW
     * @return string
     */
    public static function adminPageSubTitle($record = null, $modelClass, $show=false)
    {

        $modelClass = self::properPlural($modelClass);

        $html = '';
        $html .= '<div class="row">';
        $html .= '<div class="col-md-3"></div>';
        $html .= '<div class="col-md-6">';
        $html .= '<h1>';
        $html .= '<span class="label label-info">';

        // Edit or Create?
        if ($record)
        {
            if ($show)
            {
                $html .= 'Show the ';
            } else {
                $html .= 'Edit the ';
            }

            $html .= ucwords($modelClass);
            $html .= ': "';
            $html .= $record->title;
            $html .= '"';
        } else {
            $html .= 'Create ';
            $html .= ucwords($modelClass);
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
     * @param  array  $field   Form field array from the model
     * @return string
     */
    public static function adminFormFieldLabel($field)
    {
        // Does the field have an alternate name?
        if (!empty($field['alternate_form_name']))
        {
            $name = $field['alternate_form_name'];
        } else {
            $name = $field['name'];
        }

        if ($name == "id") return "ID";

        $html = str_replace("_", " ", $name);
        $html = ucwords($html);
        return $html;
    }

    /*
     * Create button for admin pages
     *
     * @param  string           $resource_route_name        resource route's name
     * @param  string           $button_label               button's label
     * @param  string           $pull                       bootstrap "pull" left or right
     * @return string
     */
    public static function adminCreateButton($resource_route_name, $button_label, $pull="right")
    {
        $full_url = route('admin.'.$resource_route_name.'.create');

        $button_label = self::pluralToSingular($button_label);

        $html  = '';
        $html .= '<a class="btn btn-default pull-'.$pull.'"';
        $html .= ' href="';
        $html .= $full_url;
        $html .= '" role="button">';
        $html .= '<span class="glyphicon glyphicon-heart-empty"></span>  Create '.ucwords($button_label);
        $html .= '</a><br /><br /><br />';
        return $html;
    }

    /*
     * Create button for admin pages
     *
     * @param  string           $resource_route_name        resource route's name
     * @param  string           $message                    optional button text
     * @param  string           $pull                       bootstrap "pull" left or right
     * @param  string           $icon                       specify icon, optional
     * @return string
     */
    public static function adminIndexButton($resource_route_name, $message=null, $pull="right", $icon="")
    {
        $full_url = route('admin.'.$resource_route_name.'.index');

        if (!$message) $message = $resource_route_name;

        if ($icon == "") $icon = 'glyphicon glyphicon-heart-empty';

        $html  = '';
        $html .= '<a class="btn btn-default pull-'.$pull.'"';
        $html .= ' href="';
        $html .= $full_url;
        $html .= '" role="button">';
        $html .= '<span class=".$icon."></span>  '.$message;
        $html .= '</a><br /><br /><br />';
        return $html;
    }


    /*
     * Return grammatically correct singularizations.
     *
     * @param  string           $pluralWordToCheck                   Plural word to check
     * @return string
     */
    public static function pluralToSingular($pluralWordToCheck)
    {
        $listOfSingular = [
            'categories' => 'category',
            'kb_item'    => 'knowledge base item',
            'list_email' => 'email list',
            'listlist'   => 'list',
            'people'     => 'person',
            'peoples'    => 'person',
            'social'     => 'social site',
            'todo_item'  => 'to do item',
        ];

        foreach ($listOfSingular as $plural => $singular)
        {
            if (strtolower($pluralWordToCheck) == strtolower($plural))
            {
                return $singular;
            }
        }

        return $pluralWordToCheck;
    }

    /*
     * Return grammatically correct singularizations.
     *
     * @param  string           $pluralWordToCheck                   Plural word to check
     * @return string
     */
    public static function singularToPlural($singularWordToCheck)
    {
        $listOfPlurals = [
            'category' => 'categories',
            'person'   => 'peoples',
        ];

        foreach ($listOfPlurals as $singular => $plural)
        {
            if (strtolower($singularWordToCheck) == strtolower($plural))
            {
                return $plural;
            }
        }

        return $singularWordToCheck;
    }


    /*
     * Is the plural word grammatically correct?
     *
     * Also, a method to specify custom admin form subtitle names (regular create/edit forms),
     * and called from Formhandler's AdminBaseFormController.
     *
     * @param  string           $pluralWordToCheck                   Plural word to check
     * @return string
     */
    public static function properPlural($pluralWordToCheck)
    {
        $listOfPlurals = [
            'categorys'   => 'categories',
            'kb_item'     => 'item',
            'kb_items'    => 'Items',
            'list_email'  => 'email list',
            'listlist'    => 'list',
            'people'      => 'person',
            'peoples'     => 'people',
            'postupdate'  =>'post update',
            'postupdates' =>'post updates',
            'social'      => 'social site',
            'socials'     => 'social sites',
            'todo_items'  => 'To Do items',
            'Todo_item'   => 'To Do Item',
        ];

        foreach ($listOfPlurals as $improperPlural => $correctPlural)
        {
            if (strtolower($improperPlural) == strtolower($pluralWordToCheck))
            {
                return $correctPlural;
            }
        }

        return $pluralWordToCheck;
    }



    ///////////////////////////////////////////////////////////////////
    ////////     POST UPDATE QUERY FOR USE IN BLADE FILES      ////////
    ///////////////////////////////////////////////////////////////////
    /**
     * Find the Post Updates for a given post.
     *
     * For use in blade files, as this HTMLHelper class is already passed to the view
     *
     * @param  int         $post_id        The post's ID
     * @return collection
     */
    public static function findPostupdatesByPostId($post_id)
    {
        return DB::table('postupdates')
            ->where('post_id', '=', $post_id)
            ->where('enabled', '=', 1)
            ->get();
    }


    ///////////////////////////////////////////////////////////////////
    /////                  SOCIAL MEDIA TAGS                     //////
    ///////////////////////////////////////////////////////////////////

    /**
     * Create an array of OG tags and their values for a post.
     *
     * https://developers.facebook.com/docs/sharing/webmasters
     *
     * @param  object   $post       The post object.
     * @return array
     */
    public static function createOpenGraphTagsForPost($post)
    {
       return [
            'og:title'               => $post->title,
            'og:type'                => 'article',
            'og:url'                 => $post->canonical_url,
            'og:image'               => $post->urlImage,
            'og:description'         => $post->meta_description,
            'og:site_name'           => Config::get('lasallecmsfrontend.og_site_name'),
            'article:published_time' => $post->created_at,
            'article:modified_time'  => $post->updated_at,
        ];
    }

    /**
     * Create an array of Twitter tags and their values for a post.
     *
     *  https://dev.twitter.com/cards/overview
     *
     * @param  object   $post       The post object.
     * @return array
     */
    public static function createTwitterTagsForPost($post)
    {
        return [
            'twitter:card'        => Config::get('lasallecmsfrontend.twitter_card'),
            'twitter:site'        => Config::get('lasallecmsfrontend.twitter_site'),
            'twitter:title'       => $post->title,
            'twitter:description' => $post->meta_description,
            'twitter:creator'     => Config::get('lasallecmsfrontend.twitter_card'),
            'twitter:image:src'   => $post->urlImage,
        ];
    }

    /**
     * Create an array of Google tags and their values for a post.
     *
     *  https://dev.twitter.com/cards/overview
     *
     * @param  object   $post       The post object.
     * @return array
     */
    public static function createGoogleTagsForPost($post)
    {
        return [
            'name'        => $post->title,
            'description' => $post->meta_description,
            'image'       => $post->urlImage,
        ];
    }






    ///////////////////////////////////////////////////////////////////
    ///////////////////          MISC                  ////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Make sure the URL is fully qualified
     *
     * @param   string  $url
     * @return  string
     */
    public static function prefaceURLwithHTTP($url)
    {
        $url = trim($url);
        if (substr($url, 0, 7 ) == "http://") return $url;

        if (substr($url, 0, 8 ) == "https://") return $url;

        return "http://".$url;
    }
}

