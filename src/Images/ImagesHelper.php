<?php

namespace Lasallecms\Helpers\Images;

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


    ///////////////////////////////////////////////////////////////////
    /////         MAIN METHODS FOR THE IMAGE HELPERS FOR POSTS   //////
    ///////////////////////////////////////////////////////////////////


    /**
     * Create resized image files for POSTS
     *
     * @param   string  $filename              Image's filename
     * @return  void
     */
    public function createPostResizedImageFiles($filename)
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
     * Put together the URL of the image.
     *
     * Need this for the social media tags for image.
     *
     * @param  string   $filename                 The uploaded image's filename
     * @parem  int      $width                    Width of the resized image
     * @param  int      $height                   Height of the resized image
     * @return string
     */
    public function urlOfImage($filename, $width=300, $height=300)
    {
        $url  = Config::get('app.url');
        $url .= '/';
        $url .= Config::get('lasallecmsfrontend.images_folder_resized');
        $url .= '/';
        $url .= $this->parseFilenameIntoResizedFilename($filename, $width, $height);

        return $url;
    }


    /**
     * Take an image's filename, and return the name of the resized file.
     *
     * For use within the "single post" blade file.
     *
     * Does *NOT* create the resized image file.
     *
     * @param  string   $filename                 The uploaded image's filename
     * @parem  int      $width                    Width of the resized image
     * @param  int      $height                   Height of the resized image
     * @return string
     */
    public function parseFilenameIntoResizedFilename($filename, $width=300, $height=300)
    {
        $fileNameWithNoExtension = $this->filenameWithNoExtension($filename);
        $fileNameExtension       = $this->filenameWithExtensionOnly($filename);

        $parsedFilename  = "";
        $parsedFilename .= $fileNameWithNoExtension;
        $parsedFilename .= "-";
        $parsedFilename .= $width;
        $parsedFilename .= "x";
        $parsedFilename .= "$height";
        $parsedFilename .= ".";
        $parsedFilename .= $fileNameExtension;

        return $parsedFilename;
    }



    ///////////////////////////////////////////////////////////////////
    ///       MAIN METHODS FOR THE IMAGE HELPERS FOR CATEGORIES     ///
    ///////////////////////////////////////////////////////////////////


    /**
     *  What is the category's featured image resized filename?
     *
     *  If the category does not have a featured image, then use the default featured image.
     *
     *  Return the filename of the resized featured image.
     *
     * @param   string    $categoryFeaturedImage       The category's featured image (that is in the categories table)
     * @return  string
     */
    public function getCategoryFeaturedImage($categoryFeaturedImage)
    {
        // Use the default or a specified category featured image
        $categoryFeaturedImage = $this->categoryImageDefaultOrSpecified($categoryFeaturedImage);

        // What is the full image filename that the template needs to use?
        return $this->categoryImageResizedFilename($categoryFeaturedImage);
    }

    /**
     * Is the category featured image a specified image; or, the default image?
     *
     * @param   string     $categoryFeaturedImage    The category's featured image (that is in the categories table)
     * @return  string
     */
    public function categoryImageDefaultOrSpecified($categoryFeaturedImage)
    {
        if ($categoryFeaturedImage == "") return Config::get('lasallecmsfrontend.default_category_image');

        return $categoryFeaturedImage;
    }

    /**
     * What is the name of the resized category featured image that the view will use?
     *
     * Does not actually resize the image!
     *
     * ASSUMES THAT THERE IS JUST ONE RESIZED CATEGORY IMAGE
     *
     * @param   string     $categoryFeaturedImage    The category's featured image (that is in the categories table)
     * @return  string
     */
    public function categoryImageResizedFilename($categoryFeaturedImage)
    {
        // grab the image sizes to create from the config
        $imageSizes = Config::get('lasallecmsfrontend.category_featured_image_size');

        // filename
        $fileNameWithNoExtension = $this->filenameWithNoExtension($categoryFeaturedImage);
        $fileNameExtension       = $this->filenameWithExtensionOnly($categoryFeaturedImage);

        // iterate through the image sizes, even though there is just one size
        foreach ($imageSizes as $width => $height)
        {
            return $fileNameWithNoExtension.'-'.$width.'x'.$height.'.'.$fileNameExtension;
        }
    }



    /**
     * Create resized image files for the category featured image
     *
     * @param   string  $filename          Category's UN-resized image's filename
     * @return  void
     */
    public function createCategoryResizedImageFiles($filename)
    {
        // Use the default or a specified category featured image
        $filename = $this->categoryImageDefaultOrSpecified($filename);

        // grab the image sizes to create from the config
        $imageSizes = Config::get('lasallecmsfrontend.category_featured_image_size');

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



    ///////////////////////////////////////////////////////////////////
    ///       MAIN METHODS FOR THE IMAGE HELPERS FOR TAGS           ///
    ///////////////////////////////////////////////////////////////////

    /**
     * What is the name of the resized tag default image that the view will use?
     *
     * Does not actually resize the image!
     *
     *
     * @param   string     $defaultTagImage    The defaul_tag_image config setting
     * @return  string
     */
    public function tagImageResizedFilename($defaultTagImage)
    {
        // grab the image sizes to create from the config
        $imageSizes = Config::get('lasallecmsfrontend.default_tag_image_image_size');

        // filename
        $fileNameWithNoExtension = $this->filenameWithNoExtension($defaultTagImage);
        $fileNameExtension       = $this->filenameWithExtensionOnly($defaultTagImage);

        // iterate through the image sizes$imageSizes = Config::get('lasallecmsfrontend.image_sizes');, even though there is just one size
        foreach ($imageSizes as $width => $height)
        {
            return $fileNameWithNoExtension.'-'.$width.'x'.$height.'.'.$fileNameExtension;
        }
    }



    /**
     * Create resized image files for the tag default image
     *
     * @param   string     $defaultTagImage    The defaul_tag_image config setting
     * @return  void
     */
    public function tagResizedImageFiles($defaultTagImage)
    {
        // grab the image sizes to create from the config
        $imageSizes = Config::get('lasallecmsfrontend.default_tag_image_image_size');

        // iterate through the sizes to create new image files for each size
        foreach ($imageSizes as $width => $height)
        {
            if (!$this->isFileExist($this->pathFilenameOfResizedImage($defaultTagImage, $width, $height)))
            {
                $this->resize($defaultTagImage, $width, $height);
                $this->resizeAt2x($defaultTagImage, $width, $height);
            }
        }
    }



    ///////////////////////////////////////////////////////////////////
    ///       SUPPORTING METHODS FOR THE MAIN IMAGE HELPERS         ///
    ///////////////////////////////////////////////////////////////////

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
            $constraint->aspectRatio();
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
            $constraint->aspectRatio();
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