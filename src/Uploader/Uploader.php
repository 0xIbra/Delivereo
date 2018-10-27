<?php
/**
 * Created by PhpStorm.
 * User: IbrahimTchee
 * Date: 27/08/2018
 * Time: 11:09
 */

namespace App\Uploader;


class Uploader {

    public static function upload($image, $options = [])
    {
        \Cloudinary::config([
            'cloud_name' => 'dqwg570oo',
            'api_key' => '645771516395855',
            'api_secret' => 'hOMprHGVU9sPML6aCkMjj21Olh8'
        ]);

        return \Cloudinary\Uploader::upload($image, $options);
    }

}