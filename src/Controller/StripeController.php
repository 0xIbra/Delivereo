<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\StripeClient;
use App\Utils\FlashMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Stripe\Account;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{

    /**
     * @Route("/stripe/auth", name="stripe_auth")
     * @param Request $request
     * @param ObjectManager $om
     * @param FlashBagInterface $flashBag
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function stripeAuth(Request $request, ObjectManager $om, FlashBagInterface $flashBag)
    {
        $user = $this->getUser();
        $restaurant = $user->getRestaurant();

        if (!$request->query->has('code'))
        {
            FlashMessage::message($flashBag, 'danger', 'Code stripe non trouvé');
            return $this->redirectToRoute('restaurant_info', ['restaurant' => $restaurant->getId()]);
        }


        $authorizationCode = $request->query->get('code');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://connect.stripe.com/oauth/token');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "client_secret=" . getenv('STRIPE_SECRET_KEY') . "&code=$authorizationCode&grant_type=authorization_code");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $response = json_decode($response, JSON_UNESCAPED_UNICODE);


        if (isset($response['error'])){
            FlashMessage::message($flashBag, 'danger', 'Erreur est survenue lors de la configuration de Stripe, veuillez réessayer plus tard');
            return $this->redirectToRoute('restaurant_info', ['restaurant' => $restaurant->getId()]);
        }


        $stripeClient = new StripeClient();
        $stripeClient->setAccountId($response['stripe_user_id']);
        $stripeClient->setStripePublishableKey($response['stripe_publishable_key']);

        $restaurant->setStripeClient($stripeClient);

        $restaurant->setPublished(true);

        $om->persist($restaurant);
        $om->flush();

        FlashMessage::message($flashBag, 'success', 'Stripe configuré avec succès');

        return $this->redirectToRoute('restaurant_info', ['restaurant' => $restaurant->getId()]);
    }


    /**
     * @Route("/stripe/payment")
     */
    public function tester()
    {
        Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

        $stripeClient = Account::retrieve($this->getUser()->getRestaurant()->getStripeClient()->getAccountId());

        return $this->render('dump.html.twig', ['dump' => $stripeClient]);
    }

}
