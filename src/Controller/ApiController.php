<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\Menu;
use App\Entity\Order;
use App\Entity\OrderMenu;
use App\Entity\PaymentMethod;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Utils\JSON;
use App\Utils\Validation;
use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManagerInterface;
use JMS\Serializer\SerializerInterface;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Transfer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{

    /**
     * @Route("/api/weather", name="weatherApi", methods={"GET"})
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function weather(Request $request, SerializerInterface $serializer)
    {
        if (!$request->query->has('zip'))
        {
            return JSON::JSONResponse([
                'message' => 'Le code postal n\'a pas été fourni.',
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }

        $zip = $request->query->get('zip');

        $req = curl_init();
        curl_setopt($req, CURLOPT_URL, "https://api.openweathermap.org/data/2.5/weather?zip=". $zip .",fr&lang=fr&units=metric&APPID=". getenv('OPENWEATHERMAP_API_KEY'));
        curl_setopt($req, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($req);
        $weather = json_decode($response, JSON_UNESCAPED_UNICODE);

        return JSON::JSONResponse($weather, Response::HTTP_OK, $serializer);
    }

    /**
     * @Route("/api/auth/owner/dashboard/data", name="ownerDashboardDataJson", methods={"GET"})
     *
     * @param Request $request
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return Response
     * @throws \Exception
     */
    public function ownerDashboardDataJson(Request $request, ObjectManager $om, SerializerInterface $serializer)
    {
        $restaurant = $this->getUser()->getRestaurant();
        if (!$this->isGranted('edit', $restaurant))
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'Aucun restaurant n\'a été trouvé'
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }

        $chartData = [];

        $end = new DateTime('+1 day');
        $end->setTime(0,0,2);
        $period = new DatePeriod(
            new DateTime('1 week ago'),
            new DateInterval('P1D'),
            $end
        );

        foreach ($period as $date)
        {
            $chartData['dates'][] = $date->format('d/m/Y');
            $orders = $om->getRepository(Order::class)->findRestaurantOrdersByDate($restaurant, $date);
            $revenue = 0;
            $orderCount = count($orders);
            $clients = new ArrayCollection();
            foreach ($orders as $order)
            {
                $revenue += $order->getTotalPrice();
                $consumer = $order->getConsumer();
                if (!$clients->contains($consumer))
                {
                    $clients->add($consumer);
                }
            }

            $chartData['revenue'][] = $revenue;
            $chartData['orders'][] = $orderCount;
            $chartData['clients'][] = $clients->count();
        }

        $chartData['status'] = true;

        return JSON::JSONResponseWithGroups($chartData, Response::HTTP_OK, $serializer, ['owner', 'customer', 'front']);
    }

    /**
     * @Route("/api/auth/owner/dashboard", name="ownerDashboardJson", methods={"GET"})
     *
     * @param Request $request
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function ownerDashboardJson(Request $request, ObjectManager $om, SerializerInterface $serializer)
    {
        if (!$this->isGranted('edit', $this->getUser()->getRestaurant()))
        {
            return JSON::JSONResponse([
                'message' => 'Vous n\'avez pas les droits pour accéder à cette page.',
                'status' => false
            ], Response::HTTP_UNAUTHORIZED, $serializer);
        }

        $user = $this->getUser();
        $restaurant = $user->getRestaurant();
        $recentOrders = $om->getRepository(Order::class)->recentRestaurantOrders($restaurant, 5);
        $revenue = 0;
        $orders = $restaurant->getOrders();
        $totalOrders = $orders->count();
        $clients = new ArrayCollection();
        foreach ($orders as $order)
        {
            $consumer = $order->getConsumer();
            if (!$clients->contains($consumer))
            {
                $clients->add($consumer);
            }
            $revenue += $order->getTotalPrice();
        }

        return JSON::JSONResponseWithGroups([
            'revenue' => $revenue,
            'recentOrders' => $recentOrders,
            'clients' => $clients,
            'totalOrders' => $totalOrders,
            'likes' => $restaurant->getLikes(),
            'dislikes' => $restaurant->getDislikes()
        ], Response::HTTP_OK, $serializer, ['customer', 'owner', 'front']);
    }

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
     * @Route("/api/auth/cart/checkout", name="checkoutJson", methods={"POST"})
     *
     * @param Request $request
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return Response
     * @throws \Exception
     */
    public function checkoutJson(Request $request, ObjectManager $om, SerializerInterface $serializer, \Swift_Mailer $mailer)
    {
        $cart = $this->getUser()->getCart();
        if ($cart === null || $cart->getItems()->count() === 0)
        {
            return JSON::JSONResponse([
                'message' => 'Vous n\'avez pas d\'elements dans votre panier.',
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }

        $data = json_decode($request->getContent(), JSON_UNESCAPED_UNICODE);

        if ($data['stripe'])
        {
            if (empty($data['stripeToken']))
            {
                return JSON::JSONResponse([
                    'message' => 'Bad Request.',
                    'status' => false
                ], Response::HTTP_BAD_REQUEST, $serializer);
            }
        }

        if (empty($data['deliveryAddress']) || empty($data['paymentMethod']) || empty($data['cartId']))
        {
            return JSON::JSONResponse([
                'message' => 'Mauvaise requette.',
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }

        $paymentMethod = $om->getRepository(PaymentMethod::class)->find($data['paymentMethod']['id']);
        if ($paymentMethod === null)
        {
            return JSON::JSONResponse([
                'message' => 'Erreur du mode de paiement.',
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }

        $deliveryAddress = $om->getRepository(Address::class)->find($data['deliveryAddress']['id']);
        if ($deliveryAddress === null)
        {
            return JSON::JSONResponse([
                'message' => 'Adresse de livraison fourni n\'a pas ete trouvee.',
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }

        $user = $this->getUser();
        if (!$user->hasAddress($deliveryAddress))
        {
            return JSON::JSONResponse([
                'message' => 'L\'adresse de livraison ne vous appartient pas.',
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }

        $cart = $om->getRepository(Cart::class)->find($data['cartId']);
        if ($cart === null || $cart->getConsumer() !== $user)
        {
            return JSON::JSONResponse([
                'message' => 'Erreur lors de la recuperation du panier',
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }


        $order = new Order();
        $order->setConsumer($user);
        $order->setPaymentMethod($paymentMethod);
        $order->setDeliveryAddress($deliveryAddress);
        $order->setOrderedAt(new \DateTime());

        $totalAmount = 0;
        $restaurants = [];

        foreach ($cart->getItems() as $item)
        {
            $menu = $item->getMenu();
            $totalAmount += $menu->getPrice() * $item->getQuantity();

            $orderMenu = new OrderMenu();
            $orderMenu->setMenu($menu);
            $orderMenu->setQuantity($item->getQuantity());

            $order->addItem($orderMenu);

            $restaurant = $menu->getRestaurant();
            if (!$order->getRestaurants()->contains($restaurant))
            {
                $order->addRestaurant($restaurant);
                $restaurants[] = $restaurant;
            }

            $cart->removeItem($item);
            $om->remove($item);
        }

        $order->setTotalPrice($totalAmount);

        if ($paymentMethod->getId() === PaymentMethod::CREDIT_CARD)
        {
            Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));



            if ($order->getRestaurants()->count() > 1)
            {
                $orderNumber = $order->getOrderNumber();
                $payments = Charge::create([
                    'amount' => bcmul($order->getTotalPrice(), 100) + 50,
                    'currency' => 'eur',
                    'description' => 'Commande N°'. $orderNumber,
                    'source' => $data['stripeToken'],
                    'transfer_group' => "$orderNumber"
                ]);

                foreach ($order->getRestaurants() as $restaurant)
                {
                    $restaurantAmount = 0;
                    foreach ($order->getItems() as $item)
                    {
                        if ($item->getMenu()->getRestaurant()->getId() === $restaurant->getId())
                        {
                            $restaurantAmount += ($item->getMenu()->getPrice() * $item->getQuantity());
                        }
                    }

                    $transfer = Transfer::create([
                        'amount' => bcmul($restaurantAmount, 100),
                        'currency' => 'eur',
                        'destination' => $restaurant->getStripeClient()->getAccountId(),
                        'transfer_group' => "$orderNumber"
                    ]);
                }
            } else
            {
                $payment = Charge::create([
                    'amount' => bcmul($order->getTotalPrice(), 100) + 50,
                    'currency' => 'eur',
                    'description' => 'Commande N°'. $order->getOrderNumber(),
                    'source' => $data['stripeToken'],
                    'application_fee' => 50,
                    'destination' => [
                        'account' => $restaurants[0]->getStripeClient()->getAccountId()
                    ]

                ]);
            }
        }


        $om->persist($order);
        $om->persist($cart);
        $om->flush();

        $message = (new \Swift_Message('Commande ' . $order->getOrderNumber()))
            ->setFrom('delivereo.team@gmail.com')
            ->setTo($user->getEmail())
            ->setBody($this->renderView('order/email/confirmed.html.twig', [
                'order' => $order
            ]), 'text/html');
        $mailer->send($message);




        return JSON::JSONResponse([
            'message' => 'Commande effectuée avec succès.',
            'status' => true
        ], Response::HTTP_CREATED, $serializer);
    }

    /**
     * @Route("/api/auth/cart", name="getCartJsonData", methods={"GET"})
     *
     * @param SerializerInterface $serializer
     * @param ObjectManager $om
     * @return Response
     */
    public function cart(SerializerInterface $serializer, ObjectManager $om)
    {
        $user = $this->getUser();
        if ($user === null)
        {
            return JSON::JSONResponse([
                'message' => "Vous n'êtes pas connecté.",
                'status' => false
            ], Response::HTTP_UNAUTHORIZED, $serializer);
        }

        $cart = $user->getCart();
        if ($cart === null)
        {
            $cart = new Cart();
            $user->setCart($cart);
            $om->persist($user);
            $om->flush();
        }
        $cart = $user->getCart();

        return JSON::JSONResponseWithGroups($cart, Response::HTTP_OK, $serializer, ['cart']);
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
