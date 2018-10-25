<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\DisLike;
use App\Entity\Like;
use App\Entity\Restaurant;
use App\Form\HomeSearchType;
use App\Form\SearchCustomFormType;
use App\Utils\JSON;
use App\Utils\Validation;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RestaurantController extends AbstractController
{

    /**
     * @Route("/restaurant/{restaurant}", name="restaurant_info", requirements={"restaurant"="\d+"}, methods={"GET"})
     */
    public function restaurant(Restaurant $restaurant)
    {
        if (!$this->isGranted('view', $restaurant))
        {
            return $this->redirectToRoute('homepage');
        }
        return $this->render('restaurant/index.html.twig', ['restaurant' => $restaurant]);
    }


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
     * @Route("/restaurants/{city}", name="find_restaurants_by_city", requirements={"city"="\d+"})
     * @param City $city
     * @return Response
     */
    public function findRestaurantsByCity(City $city, ObjectManager $om)
    {
        $restaurants = $om->getRepository(Restaurant::class)->findBy(['city' => $city, 'enabled' => true]);
        return $this->render('restaurant/search.html.twig', [
            'restaurants' => $restaurants,
            'city' => $city
        ]);
    }


    /**
     * @Route("/user/like/{restaurant}", name="add_like_restaurant", requirements={"restaurant"="\d+"})
     * @param Restaurant $restaurant
     * @param ValidatorInterface $validator
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addLike(Restaurant $restaurant, ValidatorInterface $validator, ObjectManager $om)
    {
        $user = $this->getUser();
        $like = new Like();

        $dislike = null;
        $dislike = $om->getRepository(DisLike::class)->findOneBy(['target' => $restaurant, 'user' => $user]);
        if ($dislike != null)
        {
            $om->remove($dislike);
        }

        $like->setTarget($restaurant);
        $like->setUser($user);
        $validation = Validation::validate($validator, $like, $this->get('session')->getFlashBag());
        if (!$validation)
        {
            return $this->redirectToRoute('restaurant_info', ['restaurant' => $restaurant->getId()]);
        }else
        {
            $om->persist($like);
            $om->flush();
            return $this->redirectToRoute('restaurant_info', ['restaurant' => $restaurant->getId()]);
        }
    }


    /**
     * @Route("/user/dislike/{restaurant}", name="add_dislike_restaurant", requirements={"restaurant"="\d+"})
     * @param Restaurant $restaurant
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addDisLike($restaurant, ValidatorInterface $validator, ObjectManager $om)
    {
        $res = $om->getRepository(Restaurant::class)->find($restaurant);
        if ($res == null)
        {
            return $this->redirectToRoute('error');
        }
        $like = null;
        $like = $om->getRepository(Like::class)->findOneBy(['target' => $restaurant, 'user' => $this->getUser()]);
        if ($like != null)
        {
            $om->remove($like);
        }
        $dislike = new DisLike();
        $dislike->setTarget($res);
        $dislike->setUser($this->getUser());
        $validation = Validation::validate($validator, $dislike, $this->get('session')->getFlashBag());
        if (!$validation)
        {
            return $this->redirectToRoute('restaurant_info', ['restaurant' => $res->getId()]);
        }else
        {
            $om->persist($dislike);
            $om->flush();
            return $this->redirectToRoute('restaurant_info', ['restaurant' => $res->getId()]);
        }
//        return $this->render('dump.html.twig', ['dump' => $like]);
    }

}
