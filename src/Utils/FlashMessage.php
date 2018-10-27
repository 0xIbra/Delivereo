<?php
/**
 * Created by PhpStorm.
 * User: ibrahim
 * Date: 26/10/18
 * Time: 11:04
 */

namespace App\Utils;


use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class FlashMessage
{

    public static function message(FlashBagInterface $flashBag, $type, $message)
    {
        $flashBag->add($type, $message);
    }

}