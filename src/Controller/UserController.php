<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Social;
use App\Entity\SocialLink;
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
     * @Route("/user/address/add", name="add_social_link", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addSocialLink(Request $request, ValidatorInterface $validator, ObjectManager $om)
    {
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
        return $this->redirectToRoute('fos_user_profile_show');
    }


    /**
     * @Route("/user/address/add", name="add_address", methods={"GET"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param ObjectManager $om
     */
    public function addAddress(Request $request, ValidatorInterface $validator, ObjectManager $om)
    {
        
    }

}
