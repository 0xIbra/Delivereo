<?php

namespace App\Controller;

use App\Entity\Category;
use App\Utils\JSON;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/categories/favourite", name="api_favourite_categories", methods={"GET"})
     */
    public function getFavouriteCategories(ObjectManager $om, SerializerInterface $serializer)
    {
        $categories = $om->getRepository(Category::class)->findBy(
            ['name' => [
                    'Asiatique',  'Fastfood', 'Dessert', 'Japonais'
                    ]
            ],
            ['id' => 'DESC']
        );
        return JSON::JSONResponse($categories, Response::HTTP_OK, $serializer);
    }


    /**
     * @Route("/api/categories", name="api_categories", methods={"GET"})
     *
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getCategories(ObjectManager $om, SerializerInterface $serializer)
    {
        $categories = $om->getRepository(Category::class)->findAll();
        return JSON::JSONResponse($categories, Response::HTTP_OK, $serializer);
    }

}
