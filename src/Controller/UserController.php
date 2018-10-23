<?php

namespace App\Controller;

use App\Entity\Social;
use App\Entity\SocialLink;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{

    /**
     * @Route("/user/address/add", name="add_address", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAddress(Request $request, ValidatorInterface $validator, ObjectManager $om)
    {
        $socialApp = $om->getRepository(Social::class)->find($request->request->get('socialtype'));
        $socialLink = new SocialLink();
        $socialLink->setType($socialApp);
        $socialLink->setUser($this->getUser());
        $socialLink->setUrl($request->request->get('socialurl'));
        $violations = $validator->validate($socialLink);
        if ($violations->count() > 0)
        {
            foreach($violations as $violation)
            {
                $this->get('session')->getFlashBag()->add('danger', $violation->getMessage());
            }
            return $this->redirectToRoute('fos_user_profile_show');
        }else
        {
            $om->persist($socialLink);
            $om->flush();
            return $this->redirectToRoute('fos_user_profile_show');
        }
    }
}
