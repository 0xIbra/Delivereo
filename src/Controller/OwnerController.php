<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\City;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Form\RestaurantFormType;
use App\Form\RestaurantModifyFormType;
use App\Utils\FlashMessage;
use App\Utils\Validation;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OwnerController extends AbstractController
{
    /**
     * @Route("/partner", name="partner")
     */
    public function index()
    {
        return $this->render('owner/index.html.twig');
    }

    /**
     * @Route("/owner/application", name="owner_application", methods={"GET", "POST"})
     * @param Request $request
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function application(Request $request, ValidatorInterface $validator, ObjectManager $om, \Swift_Mailer $mailer)
    {
        $user = $this->getUser();
        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $city = $om->getRepository(City::class)->findOneBy(['name' => $data['address']['city'], 'zipCode' => $data['address']['zipCode']]);
            $address = new Address();
            $address->setName($data['name']);
            $address->setLine1($data['address']['line1']);
            $address->setLine2($data['address']['line2']);
            $address->setCity($city);
            $restaurant->setOwner($user);
            $restaurant->setName($data['name']);
            $restaurant->setNumber($data['number']);
            $restaurant->setCity($city);
            $restaurant->setAddress($address);
            $restaurant->setOpensAt($data['opensAt']);
            $restaurant->setClosesAt($data['closesAt']);
            foreach ($data['categories'] as $category)
            {
                $restaurant->addCategory($category);
            }

            $validation = Validation::validate($validator, $restaurant, $this->get('session')->getFlashBag());
            if (!$validation)
            {
                return $this->redirectToRoute('owner_application');
            }else
            {
                if (!$user->hasRole('ROLE_OWNER'))
                {
                    $user->addRole('ROLE_OWNER');
                }
                $om->persist($restaurant);
                $om->persist($user);
                $om->flush();
                $message = (new \Swift_Message('Confirmation ' . $restaurant->getName()))
                    ->setFrom('delivereo.team@gmail.com')
                    ->setTo($user->getEmail())
                    ->setBody($this->renderView('owner/email/pending.html.twig', [
                        'user' => $user,
                        'restaurant' => $restaurant
                    ]), 'text/html');
                $mailer->send($message);
                return $this->render('owner/check_email.html.twig');
            }

        }
        return $this->render('owner/application.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/owner/dashboard", name="owner_dashboard")
     */
    public function dashboard()
    {
        if (!$this->isGranted('edit', $this->getUser()->getRestaurants()[0]))
        {
            FlashMessage::message($this->get('session')->getFlashBag(), 'danger', 'Vous n\'avez pas les droits pour accéder à cette page.');
            return $this->redirectToRoute('homepage');
        }
        return $this->render('owner/dashboard.html.twig');
    }

}
