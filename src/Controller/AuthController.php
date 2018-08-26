<?php

namespace App\Controller;

use App\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{

    /**
     * @param Request $request
     *
     * @Route("/api/register", name="json_registration", methods={"POST"})
     * @return JsonResponse
     */
    public function register(Request $request, ValidatorInterface $validator, UserManagerInterface $userManager)
    {
        $serializer = $this->get('serializer');
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $violations = $validator->validate($user);
        if ($violations->count() > 0)
        {
            $messages = [];
            foreach ($violations as $violation)
            {
                $messages[] = $violation->getMessage();
            }
            return new JsonResponse([
                'errors' => $messages
            ], Response::HTTP_BAD_REQUEST);
        }


        if ($this->createUser($user, $userManager))
        {
            $status = 'success';
            $message = "User created successfully.";
        }else
        {
            $status = 'failure';
            $message = 'Email or username already exists.';
        }

        return new JsonResponse([
            'status' => $status,
            'message' => $message
        ]);
    }


    public function createUser(User $user, UserManagerInterface $userManager)
    {
        $useremail = $userManager->findUserByEmail($user->getEmail());
        $userUsername = $userManager->findUserByUsername($user->getUsername());

        if ($useremail || $userUsername)
        {
            return false;
        }
        $user->setEnabled(true);
        $userManager->updateUser($user);
        return true;
    }

}
