<?php

namespace App\Controller;

use App\Entity\City;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\JSON;

class CityController extends AbstractController
{

    /**
     * @Route("/api/city/{city}", name="searchCity", methods={"GET"})
     * @param $city
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function searchCity($city, ObjectManager $om, SerializerInterface $serializer)
    {
        $cities = $om->getRepository(City::class)->findByName($city);
        return JSON::JSONResponse($cities, Response::HTTP_OK, $serializer);
    }

}
