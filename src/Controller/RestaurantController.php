<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\HomeSearchType;
use App\Form\SearchCustomFormType;
use App\Utils\JSON;
use Doctrine\Common\Persistence\ObjectManager;
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
    public function findRestaurantsByCityJson(City $city, SerializerInterface $serializer)
    {
        return JSON::JSONResponse($city->getRestaurants(), Response::HTTP_OK, $serializer);
    }


    /**
     * @Route("/restaurants/{city}", name="find_restaursnts_by_city", requirements={"city"="\d+"})
     * @param City $city
     */
    public function findRestaurantsByCity(City $city, ObjectManager $om)
    {
        $restaurants = $city->getRestaurants();
        return $this->render('restaurant/search.html.twig', [
            'restaurants' => $restaurants,
            'city' => $city
        ]);
    }

}
