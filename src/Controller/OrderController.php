<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderMenu;
use App\Form\CheckoutFormType;
use App\Utils\FlashMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Transfer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @param ObjectManager $om
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkout(Request $request, ObjectManager $om, \Swift_Mailer $mailer)
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


            $message = (new \Swift_Message('Commande ' . $order->getOrderNumber()))
                ->setFrom('delivereo.team@gmail.com')
                ->setTo($user->getEmail())
                ->setBody($this->renderView('order/email/confirmed.html.twig', [
                    'order' => $order
                ]), 'text/html');
            $mailer->send($message);


            Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

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


    /**
     * @Route("/user/orders/{order}", name="visit_order", requirements={"order"="\d+"})
     * @param Order|null $order
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function visitOrder(Order $order = null)
    {
        if (!$this->isGranted('view', $order))
        {
            FlashMessage::message($this->get('session')->getFlashBag(), 'danger', 'Commande non trouvée');
            return $this->redirectToRoute('owner_dashboard');
        }

        return $this->render('order/visit_order.html.twig', [
            'order' => $order
        ]);
    }
}
