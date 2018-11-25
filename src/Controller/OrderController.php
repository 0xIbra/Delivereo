<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\PaymentMethod;
use App\Form\CheckoutFormType;
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
    public function checkout(Request $request)
    {
        if (!$this->isGranted('checkout', $this->getUser()->getCart()))
        {
            return $this->redirectToRoute('cart_page');
        }

        $form = $this->createForm(CheckoutFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            return $this->render('dump.html.twig', ['dump' => [$form->getData(), $this->getUser()->getCart()]]);
        }

        return $this->render('order/checkout.html.twig', ['payment' => $form->createView()]);
    }
}
