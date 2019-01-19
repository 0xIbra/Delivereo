<?php
/**
 * Created by PhpStorm.
 * User: ibrahim
 * Date: 06/09/18
 * Time: 23:11
 */

namespace App\Utils;


use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class JSON
{

    public static function JSONResponse($data, $status, SerializerInterface $serializer)
    {
        $data = $serializer->serialize([
            'code' => $status,
            'data' => $data
        ], 'json');
        $response = new Response($data, $status);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public static function JSONResponseWithGroups($data, $status, SerializerInterface $serializer, $groups)
    {
        $data = $serializer->serialize([
            'code' => $status,
            'data' => $data
        ], 'json', SerializationContext::create()->setGroups($groups));
        $response = new Response($data, $status);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
