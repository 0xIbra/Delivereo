<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Menu;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Form\MenuFormType;
use App\Utils\FlashMessage;
use App\Utils\JSON;
use App\Utils\Validation;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MenuController extends AbstractController
{

    /**
     * @Route("/owner/restaurant/{restaurant}/menu/add", name="add_menu", requirements={"restaurant"="\d+"})
     */
    public function addMenu(Restaurant $restaurant, Request $request, ValidatorInterface $validator, ObjectManager $om, FlashBagInterface $flashBag)
    {
        if (!$this->isGranted('edit', $restaurant))
        {
            return $this->redirectToRoute('error');
        }
        $menu = new Menu();
        $menu->setRestaurant($restaurant);
        $form = $this->createForm(MenuFormType::class, $menu);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $validation = Validation::validate($validator, $menu, $flashBag);

            if (!$validation)
            {
                return $this->redirectToRoute('add_menu', ['restaurant' => $restaurant->getId()]);
            }
            $om->persist($menu);
            $om->flush();
            FlashMessage::message($flashBag, 'success', 'Menu ajouté.');
            return $this->redirectToRoute('restaurant_info', ['restaurant' => $restaurant->getId()]);
        }

        return $this->render('menu/add_menu.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $restaurant
        ]);
    }


    /**
     * @Route("/user/cart/add", name="add_to_cart", methods={"POST"})
     * @param Request $request
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addToCart(Request $request, ObjectManager $om, SerializerInterface $serializer)
    {
        if (!$this->isGranted('ROLE_CONSUMER'))
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'Vous n\'êtes pas authentifié'
            ], 404, $serializer);
        }

        $user = $this->getUser();
        $cart = $user->getCart();
        if ($cart === null)
        {
            $cart = new Cart();
        }

        $menu = $om->getRepository(Menu::class)->find($request->request->get('itemId'));
        if ($menu === null)
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'Menu indiqué n\'existe pas'
            ], 404, $serializer);
        }


        $cartItem = new CartItem();
        $cartItem->setMenu($menu);
        $cartItem->setQuantity(intval($request->request->get('quantity')));
        $cart->addItem($cartItem);

        $user->setCart($cart);
        $om->persist($user);
        $om->flush();

        return JSON::JSONResponse([
            'status' => true,
            'message' => $menu->getName() . ' a été ajouté à votre panier',
        ], 200, $serializer);
    }


    /**
     * @Route("/user/cart/remove", name="remove_from_cart", methods={"DELETE"})
     * @param Request $request
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeFromCart(Request $request, ObjectManager $om, SerializerInterface $serializer)
    {
        if (!$this->isGranted('ROLE_CONSUMER'))
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'Vous n\'êtes pas authentifié'
            ], 404, $serializer);
        }

        $cartItem = $om->getRepository(CartItem::class)->find($request->request->get('itemId'));
        if ($cartItem === null)
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'L\'element n\'a pas été trouvé'
            ], 404, $serializer);
        }

        $user = $this->getUser();
        $user->getCart()->removeItem($cartItem);
        $om->persist($user);
        $om->flush();

        return JSON::JSONResponse([
            'status' => true,
            'message' => 'L\'element a été supprimé'
        ], 200, $serializer);
    }

}
