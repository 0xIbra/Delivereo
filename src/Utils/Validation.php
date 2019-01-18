<?php
/**
 * Created by PhpStorm.
 * User: ibrahim
 * Date: 24/10/18
 * Time: 11:58
 */

namespace App\Utils;


use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validation
{

    public static function validate(ValidatorInterface $validator, $entity, FlashBagInterface $flashBag)
    {
        $violations = $validator->validate($entity);
        if ($violations->count() > 0)
        {
            foreach ($violations as $violation)
            {
                $flashBag->add('danger', $violation->getMessage());
            }
            return false;
        }else
        {
            return true;
        }
    }

}