<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\SearchCustomFormType;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function searchCity($zipCode, Request $request, ObjectManager $om)
    {
        $form = $this->createForm(SearchCustomFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $search = $form->getData()['search'];
            return $this->redirectToRoute('search_city', ['zipCode' => $search]);
        }
        $cities = $om->getRepository(City::class)->findByZipCode($zipCode);
        return $this->render('city/search.html.twig', [
            'cities' => $cities,
            'search' => $zipCode,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/city", name="city_json", methods={"GET"})
     *
     * @param Request $request
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function cityJson(Request $request, ObjectManager $om, SerializerInterface $serializer)
    {
        if (!$request->query->has('zipCode'))
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'Code postal non trouvé'
            ], 400, $serializer);
        }

        $cities = $om->getRepository(City::class)->searchByZipCode($request->query->get('zipCode'));

        return JSON::JSONResponse([
            'status' => true,
            'data' => $cities
        ], 200, $serializer);
    }

}
