<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\City;
use App\Entity\Image;
use App\Entity\Social;
use App\Entity\SocialLink;
use App\Form\AddressCustomFormType;
use App\Form\AddressFormType;
use App\Uploader\Uploader;
use App\Utils\Validation;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
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
        return $this->render('order/checkout.html.twig');
    }

    /**
     * @Route("/user/sociallink/add", name="add_social_link", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addSocialLink(Request $request, ValidatorInterface $validator, ObjectManager $om)
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('intention', $token))
        {
            $this->get('session')->getFlashBag()->add('danger', 'le CSRF token n\'est pas valide.');
            return $this->redirectToRoute('fos_user_profile_show');
        }

        $socialApp = $om->getRepository(Social::class)->find($request->request->get('socialtype'));
        $socialLink = new SocialLink();
        $socialLink->setType($socialApp);
        $socialLink->setUser($this->getUser());
        $socialLink->setUrl($request->request->get('socialurl'));
        $validation = Validation::validate($validator, $socialLink, $this->get('session')->getFlashBag());
        if (!$validation)
        {
            return $this->redirectToRoute('fos_user_profile_show');
        }else
        {
            $om->persist($socialLink);
            $om->flush();
            $this->get('session')->getFlashBag()->add('success', 'Réseau ajoutée.');
            return $this->redirectToRoute('fos_user_profile_show');
        }
    }


    /**
     * @Route("/user/image/add", name="add_image", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addImage(Request $request, ValidatorInterface $validator, ObjectManager $om)
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('intention', $token))
        {
            $this->get('session')->getFlashBag()->add('danger', 'le CSRF token n\'est pas valide.');
            return $this->redirectToRoute('fos_user_profile_show');
        }

        $user = $this->getUser();
        $file = $request->files->get('image');
        $image = new Image();
        $image->setTitle($user->getUsername() . '_' .md5(uniqid()));
        $image->setImage($file);
        $validation = Validation::validate($validator, $image, $this->get('session')->getFlashBag());
        if (!$validation)
        {
            return $this->redirectToRoute('fos_user_profile_edit');
        }else
        {
            $upload = Uploader::upload($file, ['public_id' => $image->getTitle(), 'angle' => 0, 'width' => 512]);
            $image->setUrl($upload['secure_url']);
            $user->setImage($image);
            $om->persist($user);
            $om->flush();
            $this->get('session')->getFlashBag()->add('success', 'Image ajoutée.');
            return $this->redirectToRoute('fos_user_profile_show');
        }
    }


    /**
     * @Route("/user/image/delete", name="image_delete", methods={"GET"})
     */
    public function deleteImage(ObjectManager $om)
    {
        $user = $this->getUser();
        $om->remove($user->getImage());
        $user->setImage(null);
        $om->persist($user);
        $om->flush();
        $this->get('session')->getFlashBag()->add('danger', 'Image supprimée.');
        return $this->redirectToRoute('fos_user_profile_show');
    }


    /**
     * @Route("/user/address/add", name="add_address", methods={"GET", "POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAddress(Request $request, ValidatorInterface $validator, ObjectManager $om)
    {
        $address = new Address();
        $form = $this->createForm(AddressCustomFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $city = $om->getRepository(City::class)->findOneBy(['name' => $data['city'], 'zipCode' => $data['zipCode']]);
            if ($city == null)
            {
                $this->get('session')->getFlashBag()->add('danger', 'Merci d\'entrer une ville correctement.');
                return $this->redirectToRoute('add_address');
            }
            $address->setName($data['name']);
            $address->setLine1($data['line1']);
            $address->setLine2($data['line2']);
            $address->setCity($city);
            $validation = Validation::validate($validator, $address, $this->get('session')->getFlashBag());
            if (!$validation)
            {
                return $this->redirectToRoute('add_address');
            }else
            {
                $user = $this->getUser();
                $user->addAddress($address);
                $om->persist($user);
                $om->flush();
                $this->get('session')->getFlashBag()->add('success', 'Adresse ajouté.');
                return $this->redirectToRoute('fos_user_profile_show');
            }
        }
        return $this->render('user/add_address.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/user/address/edit/{address}", name="edit_address", requirements={"address"="\d+"}, methods={"GET", "POST"})
     * @param Address $address
     * @param Request $request
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAddress(Address $address, Request $request, ObjectManager $om)
    {
        $form = $this->createForm(AddressFormType::class, $address);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $om->persist($address);
            $om->flush();
            $this->get('session')->getFlashBag()->add('success', 'La modifcation à été effectué.');
            return $this->redirectToRoute('fos_user_profile_show');
        }

        return $this->render('user/edit_address.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/user/address/delete/{address}", name="delete_address", requirements={"address"="\d+"}, methods={"GET"})
     */
    public function deleteAddress(Address $address, ObjectManager $om)
    {
        $om->remove($address);
        $om->flush();
        $this->get('session')->getFlashBag()->add('danger', 'Suppression effectuée');
        return $this->redirectToRoute('fos_user_profile_show');
    }

}
