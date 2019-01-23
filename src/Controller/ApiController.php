<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\Menu;
use App\Entity\User;
use App\Utils\JSON;
use App\Utils\Validation;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{

    /**
     * @Route("/api/auth/address/edit", name="editAddressJson", methods={"PUT"})
     *
     * @param Request $request
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function editAddressJson(Request $request, ObjectManager $om, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $address = $serializer->deserialize($request->getContent(), Address::class, 'json');
        $city = $om->getRepository(City::class)->findOneBy(['name' => $address->getCity()->getName(), 'zipCode' => $address->getCity()->getZipCode()]);
        if ($city === null)
        {
            return JSON::JSONResponse([
                'message' => 'Merci d\'indiquer une ville valide.',
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }
        $persistedAddress = $om->getRepository(Address::class)->find($address->getId());
        if ($persistedAddress === null) {
            return JSON::JSONResponse([
                'message' => 'Adresse non trouvée.',
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }
        $persistedAddress->setLine1($address->getLine1());
        $persistedAddress->setLine2($address->getLine2());
        $persistedAddress->setCity($city);
        $persistedAddress->setName($address->getName());
        $validation = Validation::validateforJson($validator, $persistedAddress);
        if (!$validation['validation'])
        {
            return JSON::JSONResponse([
                'message' => $validation['messages'],
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }


        $om->persist($persistedAddress);
        $om->flush();
        return JSON::JSONResponse([
            'message' => 'Adresse modifiée.',
            'status' => true
        ], Response::HTTP_ACCEPTED, $serializer);
    }

    /**
     * @Route("/api/auth/address/delete", name="deleteAddressJson", methods={"DELETE"})
     *
     * @param Request $request
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function deleteAddressJson(Request $request, ObjectManager $om, SerializerInterface $serializer)
    {
        if (!$request->request->has('addressId'))
        {
            return JSON::JSONResponse([
                'message' => 'Merci de fournir l\'id de l\'addresse.',
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }

        $addressId = $request->request->get('addressId');
        $address = $om->getRepository(Address::class)->find($addressId);
        if ($address === null)
        {
            return JSON::JSONResponse([
                'message' => 'L\'adresse n\'a pas été trouvée.',
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }

        $om->remove($address);
        $om->flush();

        return JSON::JSONResponse([
            'message' => 'Adresse supprimée.',
            'status' => true
        ], Response::HTTP_ACCEPTED, $serializer);
    }

    /**
     * @Route("/api/auth/address/add", name="addAddressJson", methods={"POST"})
     *
     * @param Request $request
     * @param ObjectManager $om
     * @param UserManagerInterface $userManager
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function addAddressJson(Request $request, ObjectManager $om, UserManagerInterface $userManager, SerializerInterface $serializer)
    {
        $address = $serializer->deserialize($request->getContent(), Address::class, 'json');

        $city = $om->getRepository(City::class)->findOneBy(['name' => $address->getCity()->getName(), 'zipCode' => $address->getCity()->getZipCode()]);
        if ($city === null)
        {
            return JSON::JSONResponse([
                'message' => 'Merci d\'entrer les données d\'une ville valide.',
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }
        $address->setCity($city);

        $user = $this->getUser();
        $user->addAddress($address);
        $userManager->updateUser($user);
        return JSON::JSONResponse([
            'message' => 'Le nouveau adresse a été ajouté.',
            'status' => true
        ], Response::HTTP_CREATED, $serializer);
    }

    /**
     * @Route("/api/categories/favourite", name="api_favourite_categories", methods={"GET"})
     *
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return Response
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


    /**
     * @Route("/api/auth/cart", name="getCartJsonData", methods={"GET"})
     *
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function cart(SerializerInterface $serializer)
    {
        $user = $this->getUser();
        if ($user === null)
        {
            return JSON::JSONResponse([
                'message' => "Vous n'êtes pas connecté.",
                'status' => false
            ], Response::HTTP_UNAUTHORIZED, $serializer);
        }

        return JSON::JSONResponseWithGroups($user->getCart(), Response::HTTP_OK, $serializer, ['cart']);
    }



    /**
     * @Route("/api/auth/cart/add", name="addToCartJson", methods={"POST"})
     *
     * @param Request $request
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addToCartJson(Request $request, ObjectManager $om, SerializerInterface $serializer)
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
            ], Response::HTTP_NOT_FOUND, $serializer);
        }

        if (!$cart->getItems()->isEmpty())
        {
            foreach ($cart->getItems() as $item)
            {
                if ($menu === $item->getMenu())
                {
                    $quantity = $item->getQuantity() + intval($request->request->get('quantity'));
                    $item->setQuantity($quantity);
                    $cart->addItem($item);
                    $user->setCart($cart);
                    $om->persist($user);
                    $om->flush();

                    return JSON::JSONResponse([
                        'status' => true,
                        'message' => 'La quantité du Menu "'. $menu->getName() . '" a été augmenté.',
                    ], 200, $serializer);
                }
            }
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
        ], Response::HTTP_ACCEPTED, $serializer);
    }


    /**
     * @Route("/api/auth/cart/remove", name="removeFromCartJson", methods={"DELETE"})
     *
     * @param Request $request
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeFromCartJson(Request $request, ObjectManager $om, SerializerInterface $serializer)
    {
        if (!$this->isGranted('ROLE_CONSUMER'))
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'Vous n\'êtes pas authentifié'
            ], Response::HTTP_UNAUTHORIZED, $serializer);
        }

        $cartItem = $om->getRepository(CartItem::class)->find($request->request->get('itemId'));
        if ($cartItem === null)
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'L\'element n\'a pas été trouvé'
            ], Response::HTTP_NOT_FOUND, $serializer);
        }

        $om->remove($cartItem);
        $om->flush();

        return JSON::JSONResponse([
            'status' => true,
            'message' => 'L\'element a été supprimé'
        ], Response::HTTP_ACCEPTED, $serializer);
    }



    /**
     * @Route("/api/auth/cart/increase", name="increaseCartItemJson", methods={"PUT"})
     *
     * @param Request $request
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function increaseCartItem(Request $request, ObjectManager $om, SerializerInterface $serializer)
    {
        $cartItem = $om->getRepository(CartItem::class)->find(intval($request->request->get('itemId')));
        if ($cartItem === null)
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'L\'element n\'a pas été trouvé'
            ], 400, $serializer);
        }

        $user = $this->getUser();
        $cart = $user->getCart();
        if ($cart === null)
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'Votre panier est vide'
            ], 400, $serializer);
        }

        if ($cartItem->getQuantity() === 20)
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'Quantité maximale atteinte'
            ], 200, $serializer);
        }

        $quantity = $cartItem->getQuantity() + 1;
        $cartItem->setQuantity($quantity);
        $cart->addItem($cartItem);
        $om->persist($cart);
        $om->flush();

        $newPrice = $cartItem->getMenu()->getPrice() * $cartItem->getQuantity();
        $total = 0;

        foreach ($cart->getItems() as $item)
        {
            $total += ($item->getMenu()->getPrice() * $item->getQuantity());
        }

        return JSON::JSONResponse([
            'status' => true,
            'quantity' => $cartItem->getQuantity(),
            'newPrice' =>  $newPrice,
            'totalPrice' => $total,
            'message' => 'La quantité de l\'element a été incrementé'
        ], Response::HTTP_ACCEPTED, $serializer);
    }




    /**
     * @Route("/api/auth/cart/decrease", name="decreaseCartItemJson", methods={"PUT"})
     *
     * @param Request $request
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function decreaseCartItem(Request $request, ObjectManager $om, SerializerInterface $serializer)
    {
        $cartItem = $om->getRepository(CartItem::class)->find(intval($request->request->get('itemId')));
        if ($cartItem === null)
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'L\'element n\'a pas été trouvé'
            ], 400, $serializer);
        }

        $user = $this->getUser();
        $cart = $user->getCart();
        if ($cart === null)
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'Votre panier est vide'
            ], 400, $serializer);
        }

        if ($cartItem->getQuantity() === 1)
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'Quantité minimale atteinte'
            ], 200, $serializer);
        }

        $quantity = $cartItem->getQuantity() - 1;
        $cartItem->setQuantity($quantity);
        $cart->addItem($cartItem);
        $om->persist($cart);
        $om->flush();

        $newPrice = $cartItem->getMenu()->getPrice() * $cartItem->getQuantity();
        $total = 0;

        foreach ($cart->getItems() as $item)
        {
            $total += ($item->getMenu()->getPrice() * $item->getQuantity());
        }

        return JSON::JSONResponse([
            'status' => true,
            'quantity' => $cartItem->getQuantity(),
            'newPrice' =>  $newPrice,
            'totalPrice' => $total,
            'message' => 'La quantité de l\'element a été diminué'
        ], Response::HTTP_ACCEPTED, $serializer);
    }

}
