<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\Restaurant;
use App\Form\MenuFormType;
use App\Utils\FlashMessage;
use App\Utils\Validation;
use Doctrine\Common\Persistence\ObjectManager;
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
}
