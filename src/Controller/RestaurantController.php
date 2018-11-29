<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\DisLike;
use App\Entity\Image;
use App\Entity\Like;
use App\Entity\Restaurant;
use App\Form\HomeSearchType;
use App\Form\RestaurantModifyFormType;
use App\Form\SearchCustomFormType;
use App\Uploader\Uploader;
use App\Utils\FlashMessage;
use App\Utils\JSON;
use App\Utils\Validation;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\SerializerInterface;
use Stripe\Account;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RestaurantController extends AbstractController
{

    /**
     * @Route("/restaurant/{restaurant}", name="restaurant_info", requirements={"restaurant"="\d+"}, methods={"GET"})
     * @param Restaurant $restaurant
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function restaurant(Restaurant $restaurant, ObjectManager $om)
    {
        if (!$this->isGranted('view', $restaurant))
        {
            return $this->redirectToRoute('homepage');
        }

        $stripe = null;

        if ($this->isGranted('ROLE_OWNER') && $restaurant->getStripeClient() !== null)
        {
            Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));
            $stripe = Account::retrieve($restaurant->getStripeClient()->getAccountId());
        }

        return $this->render('restaurant/index.html.twig', [
            'restaurant' => $restaurant,
            'stripe' => $stripe
        ]);
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
        $restaurants = $om->getRepository(Restaurant::class)->findBy(['city' => $city, 'enabled' => true, 'published' => true]);
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


    /**
     * @Route("/owner/restaurant/edit/{restaurant}", name="edit_restaurant", requirements={"restaurant"="\d+"})
     * @param Restaurant $restaurant
     * @param ValidatorInterface $validator
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editRestaurant(Restaurant $restaurant, Request $request, ValidatorInterface $validator, ObjectManager $om)
    {
        if (!$this->isGranted('edit', $restaurant))
        {
            return $this->redirectToRoute('fos_user_profile_show');
        }
        $form = $this->createForm(RestaurantModifyFormType::class, $restaurant);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $validation = Validation::validate($validator, $restaurant, $this->get('session')->getFlashBag());
            if (!$validation)
            {
                return $this->redirectToRoute('edit_restaurant', ['restaurant' => $restaurant->getId()]);
            }
            $om->persist($restaurant);
            $om->flush();
            return $this->redirectToRoute('restaurant_info', ['restaurant' => $restaurant->getId()]);
        }

        return $this->render('restaurant/edit.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $restaurant
        ]);
    }


    /**
     * @Route("/owner/restaurant/{restaurant}/image/add", name="add_restaurant_image", requirements={"restaurant"="\d+"})
     * @param Restaurant $restaurant
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addRestaurantImage(Restaurant $restaurant, Request $request, ValidatorInterface $validator, ObjectManager $om)
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('intention', $token))
        {
            $this->get('session')->getFlashBag()->add('danger', 'le CSRF token n\'est pas valide.');
            return $this->redirectToRoute('edit_restaurant', ['restaurant' => $restaurant->getId()]);
        }

        if (!$this->isGranted('edit', $restaurant))
        {
            return $this->redirectToRoute('restaurant_info', ['restaurant' => $restaurant->getId()]);
        }

        $file = $request->files->get('image');
        $image = new Image();
        $image->setImage($file);
        $image->setTitle($restaurant->getId() . '_' . md5(uniqid()));

        $validation = Validation::validate($validator, $image, $this->get('session')->getFlashBag());
        if (!$validation)
        {
            return $this->redirectToRoute('edit_restaurant', ['restaurant' => $restaurant->getId()]);
        }else
        {
            $upload = Uploader::upload($file, ['public_id' => $image->getTitle(), 'angle' => 0, 'width' => 512]);
            $image->setUrl($upload['secure_url']);
            $restaurant->setImage($image);
            $om->persist($restaurant);
            $om->flush();
            FlashMessage::message($this->get('session')->getFlashBag(), 'success', 'Image ajoutée.');
            return $this->redirectToRoute('restaurant_info', ['restaurant' => $restaurant->getId()]);
        }
    }


    /**
     * @Route("/owner/restaurant/{restaurant}/image/delete", name="delete_restaurant_image", requirements={"restaurant"="\d+"})
     * @param Restaurant $restaurant
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteRestaurantImage(Restaurant $restaurant, ObjectManager $om)
    {
        if (!$this->isGranted('edit', $restaurant))
        {
            return $this->redirectToRoute('restaurant_info', ['restaurant' => $restaurant->getId()]);
        }

        $om->remove($restaurant->getImage());
        $restaurant->setImage(null);
        $om->persist($restaurant);
        $om->flush();
        FlashMessage::message($this->get('session')->getFlashBag(), 'danger', 'Image supprimée.');
        return $this->redirectToRoute('restaurant_info', ['restaurant' => $restaurant->getId()]);
    }

}
