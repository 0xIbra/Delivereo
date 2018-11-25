<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{

    /**
     * @Route("/stripe/auth", name="stripe_auth")
     */
    public function stripeAuth(Request $request)
    {
        $user = $this->getUser();


    }

}
