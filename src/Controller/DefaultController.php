<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Form\HomeSearchType;
use Cloudinary\Uploader;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     * @param ObjectManager $manager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, ObjectManager $manager)
    {
        $categories = $manager->getRepository(Category::class)->findBy(['name' => ['Burger', 'Pizza', 'Fast food', 'Desserts']]);
        $searchform = $this->createForm(HomeSearchType::class);
        $searchform->handleRequest($request);
        if ($searchform->isSubmitted() && $searchform->isValid())
        {
            $data = $searchform->getData();
            $data = $data['search'];
            return $this->redirectToRoute('search_city', ['zipCode' => $data]);
        }
        return $this->render('home.html.twig',
            [
                'form' => $searchform->createView(),
                'categories' => $categories
            ]
        );
    }



}
