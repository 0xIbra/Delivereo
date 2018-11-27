<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderMenu;
use App\Entity\PaymentMethod;
use App\Form\CheckoutFormType;
use App\Utils\FlashMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Transfer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/user/cart", name="cart_page")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cart()
    {
        return $this->render('order/cart.html.twig');
    }


    /**
     * @Route("/user/checkout", name="checkout_page")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkout(Request $request, ObjectManager $om)
    {
        if (!$this->isGranted('checkout', $this->getUser()->getCart()))
        {
            return $this->redirectToRoute('cart_page');
        }

        $form = $this->createForm(CheckoutFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            if (!$request->request->has('stripeToken'))
            {
                FlashMessage::message($this->get('session')->getFlashBag(), 'danger', 'Carte bancaire n\'est existe pas ou est invalide');
                return $this->redirectToRoute('checkout_page');
            }

            $total = 0;
            $user = $this->getUser();
            $cart = $user->getCart();

            $restaurants = null;

            $stripeToken = $request->request->get('stripeToken');

            $data = $form->getData();
            $order = new Order();
            $order->setConsumer($user);
            $order->setDeliveryAddress($data['address']);
            $order->setPaymentMethod($data['paymentMethod']);

            foreach ($cart->getItems() as $item)
            {
                $total += ($item->getMenu()->getPrice() * $item->getQuantity());

                $orderMenu = new OrderMenu();
                $orderMenu->setQuantity($item->getQuantity());
                $orderMenu->setMenu($item->getMenu());
                $order->addItem($orderMenu);

                $restaurant = $item->getMenu()->getRestaurant();
                if (!$order->getRestaurants()->contains($restaurant))
                {
                    $order->addRestaurant($restaurant);
                }

                $cart->removeItem($item);
                $om->remove($item);
            }

            $order->setTotalPrice($total);

            $om->persist($order);
            $om->persist($cart);
            $om->flush();


            Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));
//            $payment = Charge::create([
//                'amount' => bcmul($total, 100),
//                'currency' => 'eur',
//                'description' => 'Commande N°'. $order->getId(),
//                'source' => $stripeToken,
//                'application_fee' => 50,
//                'transfer_group'
//
//            ]);

            $restaurants = $order->getRestaurants();

            if ($restaurants->count() > 1)
            {
                $total = 0;
                foreach ($order->getItems() as $item)
                {
                    $total += ($item->getMenu()->getPrice() * $item->getQuantity());
                }

                $orderNumber = $order->getOrderNumber();
                $payments = Charge::create([
                    'amount' => bcmul($total, 100),
                    'currency' => 'eur',
                    'description' => 'Commande N°'. $orderNumber,
                    'source' => $stripeToken,
                    'transfer_group' => "$orderNumber"

                ]);

                foreach ($restaurants as $restaurant)
                {
                    $total = 0;
                    foreach ($order->getItems() as $item)
                    {
                        if ($item->getMenu()->getRestaurant() === $restaurant)
                        {
                            $total += ($item->getMenu()->getPrice() * $item->getQuantity());
                        }
                    }

                    $transfer = Transfer::create([
                        'amount' => bcmul($total, 100),
                        'currency' => 'eur',
                        'destination' => $restaurant->getStripeClient()->getAccountId(),
                        'transfer_group' => "$orderNumber"
                    ]);
                }

            }else
            {
                $total = 0;
                foreach ($order->getItems() as $item)
                {
                    if ($item->getMenu()->getRestaurant() === $restaurant)
                    {
                        $total += ($item->getMenu()->getPrice() * $item->getQuantity());
                    }
                }

                $payment = Charge::create([
                    'amount' => bcmul($total, 100),
                    'currency' => 'eur',
                    'description' => 'Commande N°'. $order->getOrderNumber(),
                    'source' => $stripeToken,
                    'application_fee' => 50,
                    'destination' => [
                        'account' => $restaurants->first()->getStripeClient()->getAccountId()
                    ]

                ]);
            }

            return $this->render('order/complete.html.twig', ['order' => $order]);
        }

        return $this->render('order/checkout.html.twig', ['payment' => $form->createView()]);
    }
}
