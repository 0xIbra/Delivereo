<?php

namespace App\Controller;

use App\Entity\City;
use App\Utils\JSON;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RestaurantController extends AbstractController
{

    /**
     * @Route("/api/restaurant/{city}", name="restaurantsByCity", methods={"GET"}, requirements={"city"="\d+"})
     * @param City $city
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function findRestaurantsByCity(City $city, SerializerInterface $serializer)
    {
        return JSON::JSONResponse($city->getRestaurants(), Response::HTTP_OK, $serializer);
    }

}
