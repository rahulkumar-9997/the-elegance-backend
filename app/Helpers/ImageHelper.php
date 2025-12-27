<?php

namespace App\Helpers;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
class ImageHelper
{
    private static $manager = null;
    public static function getManager()
    {
        if (self::$manager === null) {
            self::$manager = new ImageManager(new Driver());
        }
        return self::$manager;
    }

    public static function saveAsWebp($file, $path, $quality = 75)
    {
        $manager = self::getManager();
        $image = $manager->read($file);
        $image->toWebp($quality)->save($path);
        return true;
    }
    public static function resizeAndSave($file, $path, $width = null, $height = null, $quality = 75)
    {
        $manager = self::getManager();
        $image = $manager->read($file);        
        if ($width && $height) {
            $image->cover($width, $height);
        }        
        $image->toWebp($quality)->save($path);
        return true;
    }
}