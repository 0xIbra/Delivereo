<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\SearchCustomFormType;
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
    public function searchCityJson($city, ObjectManager $om, SerializerInterface $serializer)
    {
        $cities = $om->getRepository(City::class)->findByName($city);
        return JSON::JSONResponse($cities, Response::HTTP_OK, $serializer);
    }

    /**
     * @Route("/city/{zipCode}", name="search_city", requirements={"zipCode"});
     * @param $search
     * @param ObjectManager $om
     * @return Response
     */
    public function searchCity($zipCode, ObjectManager $om)
    {
        $form = $this->createForm(SearchCustomFormType::class);
        $cities = $om->getRepository(City::class)->findByZipCode($zipCode);
        return $this->render('city/search.html.twig', [
            'cities' => $cities,
            'search' => $zipCode,
            'form' => $form->createView()
        ]);
    }

}
