<?php

namespace Lasallecms\Helpers\Images;

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

// Laravel facades
use Illuminate\Support\Facades\Config;

// Laravel classes
use Illuminate\Filesystem\Filesystem;

// Third party classes
use Intervention\Image\Facades\Image;

/*
 * Images helper
 */
class ImagesHelper
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;


    /**
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }


    /**
     * Create resized image files
     *
     * @param   string  $filename      Image filename
     * @return  void
     */
    public function createResizedImageFiles($filename)
    {
        // grab the image sizes to create from the config
        $imageSizes = Config::get('lasallecmsfrontend.image_sizes');

        // iterate through the sizes to create new image files for each size
        foreach ($imageSizes as $width => $height)
        {
            if (!$this->isFileExist($this->pathFilenameOfResizedImage($filename, $width, $height)))
            {
                $this->resize($filename, $width, $height);
                $this->resizeAt2x($filename, $width, $height);
            }
        }
    }

    /**
     * Resize the image using the terrific Intervention package
     *
     * @param   string  $filename      Image filename
     * @param   int     $width         Resized width
     * @param   int     $height        Resized height
     * @return  void
     */
    public function resize($filename, $width, $height)
    {
        // open an image file
        $img = Image::make($this->pathOfImagesUploadFolder() .'/'. $filename);

        // resize image
        // http://image.intervention.io/api/resize
        // closure prevents possible upsizing
        $img->resize($width, $height, function ($constraint) {
            $constraint->upsize();
        });

        $img->save($this->pathFilenameOfResizedImage($filename, $width, $height));
    }

    /**
     * Resize the image using the terrific Intervention package for the retinajs plugin
     * (http://imulus.github.io/retinajs/)
     *
     * @param   string  $filename      Image filename
     * @param   int     $width         Resized width
     * @param   int     $height        Resized height
     * @return  void
     */
    public function resizeAt2x($filename, $width, $height)
    {
        // open an image file
        $img = Image::make($this->pathOfImagesUploadFolder() .'/'. $filename);

        $widthAt2x  = $width * 2;
        $heightAt2x = $height * 2;

        // resize image
        // http://image.intervention.io/api/resize
        // closure prevents possible upsizing
        $img->resize($widthAt2x , $heightAt2x, function ($constraint) {
            $constraint->upsize();
        });

        $img->save($this->pathFilenameOfResizedImageAt2x($filename, $width, $height));
    }


    /**
     * Put together the path + name of the resized imaged.
     *
     * @param   string  $filename      Image filename
     * @param   int     $width         Resized width
     * @param   int     $height        Resized height
     * @return  string
     */
    public function pathFilenameOfResizedImage($filename, $width, $height)
    {
        $path                      = $this->pathOfImagesResizedFolder();
        $filenameWithNoExtension   = $this->filenameWithNoExtension($filename);
        $filenameWithExtensionOnly = $this->filenameWithExtensionOnly($filename);

        $resizedFilename  = $path;
        $resizedFilename .= "/";
        $resizedFilename .= $filenameWithNoExtension;
        $resizedFilename .= '-';
        $resizedFilename .= $width;
        $resizedFilename .= 'x';
        $resizedFilename .= $height;
        $resizedFilename .= '.';
        $resizedFilename .= $filenameWithExtensionOnly;

        return $resizedFilename;
    }

    /**
     * Put together the path + name of the resized imaged for the retinajs plugin.
     * (http://imulus.github.io/retinajs/)
     *
     * @param   string  $filename      Image filename
     * @param   int     $width         Resized width
     * @param   int     $height        Resized height
     * @return  string
     */
    public function pathFilenameOfResizedImageAt2x($filename, $width, $height)
    {
        $path                      = $this->pathOfImagesResizedFolder();
        $filenameWithNoExtension   = $this->filenameWithNoExtension($filename);
        $filenameWithExtensionOnly = $this->filenameWithExtensionOnly($filename);

        $resizedFilename  = $path;
        $resizedFilename .= "/";
        $resizedFilename .= $filenameWithNoExtension;
        $resizedFilename .= '-';
        $resizedFilename .= $width;
        $resizedFilename .= 'x';
        $resizedFilename .= $height;
        $resizedFilename .= '@2x.';
        $resizedFilename .= $filenameWithExtensionOnly;

        return $resizedFilename;
    }

    /**
     * Filename sans extension
     *
     * @param    string        $filename
     * @return   string
     */
    public function filenameWithNoExtension($filename)
    {
        return substr($filename, 0, strlen($filename) -4 );
    }

    /**
     * Filename's extension.
     *
     * Assumes a three character extension.
     *
     * @param    string        $filename
     * @return   string
     */
    public function filenameWithExtensionOnly($filename)
    {
        return substr($filename,strlen($filename) -3,3);
    }

    /**
     * Path of images upload folder
     * @param   string  $filename      Image filename
     * @param   int     $width         Resized width
     * @param   int     $height        Resized height
     * @return string
     */
    public function pathOfImagesUploadFolder()
    {
        return public_path() .'/'. Config::get('lasallecmsfrontend.images_folder_uploaded');
    }

    /**
     * Path of images resized folder
     *
     * @return string
     */
    public function pathOfImagesResizedFolder()
    {
        return public_path() .'/'. Config::get('lasallecmsfrontend.images_folder_resized');
    }

    /**
     * Does this file exist?
     *
     * @param   string   $file    Full path and filename of file
     * @return  bool
     */
     public function isFileExist($file)
     {
         if ($this->files->isFile($file)) return true;

         return false;
     }
}