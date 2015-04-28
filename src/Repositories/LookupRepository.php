<?php namespace Lasallecms\Helpers\Repositories;

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
 *
 */



use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Carbon\Carbon;


use Illuminate\Container\Container;


class LookupRespository {

    /*
     * @var Illuminate\Container\Container
     */
    protected $app;

    /*
     * @var  namespace and class of relevant model
     */
    protected $model;


    public function __construct(Container $app)
    {
        // Inject the container in order to inject the relevant model
        $this->app = $app;
    }


    public function injectModelIntoRepository($modelNamespaceClass)
    {
        // Inject the container, then use the container to inject the model object
        // "Resolve something out of the container"
        //  http://laravel.com/docs/5.0/container#basic-usage
        $this->model = $this->app->make($modelNamespaceClass);
    }


    /*
     * Return entire collection
     *
     *  @return eloquent
     */
    public function getAll()
    {
        return $this->model->all();
    }


    /*
     * Return specific model
     *
     * @param id         Post ID
     * @return eloquent
     */
    public function getFind($id)
    {
        return $this->model->findOrfail($id);
    }


    /*
     * Create model
     *
     * @param  data     Input data
     * @return eloquent
     */
    public function getCreate($data)
    {
        return $this->model->create($data);
    }


    /*
     * Store model
     *
     * @param  data     Input data
     * @return eloquent
     */
    public function getStore($data)
    {
        return $this->model->store($data);
    }


    /*
    * Save model
    *
    * @return eloquent
    */
    public function getSave()
    {
        return $this->model->save();
    }


    /*
    * Update model
    *
    * @param  data     array  Input data
    * @return eloquent
    */
    public function getUpdate($data)
    {
        return $this->model->update($data);
    }


    /*
     * Delete a model
     *
     * @param id         Post ID
     * @return eloquent
     */
    public function getDestroy($id)
    {
        return $this->model->destroy($id);
    }


    /*
     * Lists
     *
     * @param id         Post ID
     * @return eloquent
     */
    public function getLists($name, $id)
    {
        //return $this->model->lists($name, $id)->orderBy('title', 'ASC');
        return $this->model->lists($name, $id);
    }



    ///////////////////////////////////////////////////////////////////
    ///////////////////////////  LOCK FIELDS  /////////////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Unlock records belonging to the current user.
     *
     * @param  string  $tableName
     * @return bool
     */
    public function unlockMyRecords($tableName)
    {
        $results = $this->lockedRecordsByUser($tableName, Auth::user()->id);

        foreach($results as $result)
        {
            $this->unpopulateLockFields($result->id);
        }
    }

    /*
     * Collection of records that are locked by a specific user, for a specific table
     *
     * @param  string     $tableName
     * @param  int        $userId
     * @return collection
     */
    public function lockedRecordsByUser($tableName, $userId)
    {
        return DB::table($tableName)->where('locked_by', '=', $userId)->get();
    }

    /*
     * Is the record locked?
     * "Locked" is defined as the 'locked_by' field being populated; that is,> 0
     *
     * @param  int     $id
     * @return bool
     */
    public function isLocked($id)
    {
        $record = $this->model->findOrFail($id);

        if ($record->locked_by > 0) return true;

        return false;
    }

    /*
     * Populate the locked_at and locked_by fields.
     * By definition, this must be an UPDATE
     *
     * All that is needed is the ID
     *
     * @param  int     $id
     * @return bool
     */
    public function populateLockFields($id)
    {
        // $this->getSave($data);   --> creates new record ;-(
        // $this->getUpdate($data); --> integrity constraint violation: 1451 Cannot delete or
        //                              update a parent row: a foreign key constraint fails  ;-(
        // use the model, not the repository, to UPDATE
        $record = $this->model->findOrFail($id);

        $record->locked_by = Auth::user()->id;
        $record->locked_at = date('Y-m-d H:i:s');

        return $record->save();
    }

    /*
     * Un-populate the locked_at and locked_by fields.
     * By definition, this must be an UPDATE
     *
     * All that is needed is the ID
     *
     * @param  int     $id
     * @return mixed(?)
     */
    public function unpopulateLockFields($id)
    {
        // $this->getSave($data);   --> creates new record ;-(
        // $this->getUpdate($data); --> integrity constraint violation: 1451 Cannot delete or
        //                              update a parent row: a foreign key constraint fails  ;-(
        // use the model, not the repository, to UPDATE
        $record = $this->model->findOrFail($id);

        // Locked by field allowed to be null
        $record->locked_by = null;
        $record->locked_at = null;

        return $record->save();
    }


    ///////////////////////////////////////////////////////////////////
    ///////////////////////////  SANITATION   /////////////////////////
    ///////////////////////////////////////////////////////////////////
    /*
     * Get sanitation array for INSERT from model
     *
     * @return array
     */
    public function getSanitationRulesForCreate()
    {
        return [
            'title'            => 'trim|strip_tags',
            'description'      => 'trim',
        ];
    }

    /*
     * Get sanitation array for UPDATE from model
     *
     * @return array
     */
    public function getSanitationRulesForUpdate()
    {
        return [
            'title'            => 'trim|strip_tags',
            'description'      => 'trim',
        ];
    }

    /*
     * Sanitize
     *
     * @param  array  $data
     * @param  array  $rules
     * @return array
     */
    public function getSanitize($data, $rules)
    {
        // iterate through each field
        foreach ($rules as $field => $rule)
        {
            // turn the listing of rules with a "|" separator into an array
            // yeah, $rule can contain multiple rules (ie, multiple php functions)
            $phpFunctions = explode('|', $rule);

            // iterate through each rule
            foreach($phpFunctions as $phpFunction)
            {
                $data[$field] = call_user_func_array($phpFunction, [$data[$field] ]);

                // debug
                //echo "<br>The field ".$field." is now = ".$data[$field]." (".$singleFunction.")";
            }
        }

        return $data;
    }


    ///////////////////////////////////////////////////////////////////
    ///////////////////////////  VALIDATION   /////////////////////////
    ///////////////////////////////////////////////////////////////////
    /*
     * Get validation array for INSERT from model
     *
     * @return array
     */
    public function getValidationRulesForCreate()
    {
        return [
            'title'            => 'required|min:4|unique:lookup_address_types',
            'description'      => 'min:11',
            'enabled'          => 'boolean',
        ];
    }

    /*
     * Get validation array for UPDATE from model
     *
     * @return array
     */
    public function getValidationRulesForUpdate()
    {
        return [
            'id'               => 'exists:id',
            'title'            => 'required|min:4',
            'description'      => 'min:11',
            'enabled'          => 'boolean',
        ];
    }




    ///////////////////////////////////////////////////////////////////
    ////////////////    PREPARE FOR PERSIST     ///////////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Transform title for persist.
     *
     * @param  text  $title
     * @return text
     */
    public function prepareTitleForPersist($title)
    {
        // Strip whitespace (or other characters) from the beginning and end of a string
        $transformedTitle = trim($title);

        // Strip HTML and PHP tags from a string
        $transformedTitle = strip_tags($transformedTitle);

        // Strip tags, optionally strip or encode special characters
        // http://php.net/manual/en/filter.filters.sanitize.php
        $transformedTitle = filter_var($transformedTitle, FILTER_SANITIZE_STRING);

        // Uppercase the first character of each word in a string
        $transformedTitle = ucwords($transformedTitle);

        return $transformedTitle;
    }


    /*
     * Transform description for persist.
     *
     * @param  text  $meta_description
     * @param  text  $excerpt
     * @return text
     */
    public function prepareDescriptionForPersist($description)
    {
        $description = html_entity_decode($description);
        $description = strip_tags($description);
        $description = filter_var($description, FILTER_SANITIZE_STRING);

        // remove the encoded blank chars
        $description = str_replace("\xc2\xa0",'',$description);

        $description = trim($description);
        return $description;
    }


}