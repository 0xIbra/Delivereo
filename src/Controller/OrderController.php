<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\PaymentMethod;
use App\Form\CheckoutFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkout()
    {
        $form = $this->createForm(CheckoutFormType::class);
        return $this->render('order/checkout.html.twig', ['payment' => $form->createView()]);
    }
}
