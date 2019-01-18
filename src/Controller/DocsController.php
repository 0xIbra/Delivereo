<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DocsController extends AbstractController
{
    /**
     * @Route("/docs/stripe", name="docs_stripe")
     */
    public function stripe()
    {
        return $this->render('docs/stripe.html.twig');
    }
}
