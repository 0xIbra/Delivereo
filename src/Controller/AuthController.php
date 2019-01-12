<?php

namespace App\Controller;

use App\Entity\User;
use App\Utils\JSON;
use FOS\UserBundle\Model\UserManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{


    /**
     * @param SerializerInterface $serializer
     * @return Response
     *
     * @Route("/api/auth/check", name="api_check_auth", methods={"POST"})
     */
    public function isAuth(SerializerInterface $serializer)
    {
        if ($this->getUser() === null)
        {
            return JSON::JSONResponse([
                'status' => true,
                'code' => Response::HTTP_OK,
                'message' => 'Vous êtes connecté'
            ], Response::HTTP_OK, $serializer);
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @return Response
     *
     * @Route("/api/auth/me", name="get_user", methods={"GET", "POST"})
     */
    public function me(SerializerInterface $serializer)
    {
        $res = [
            'code' => Response::HTTP_OK,
            'user' => $this->getUser()
        ];
        $response = new Response($serializer->serialize($res, 'json'));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

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
                'errors' => $messages,
                'code' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }


        if ($this->createUser($user, $userManager))
        {
            $code = Response::HTTP_CREATED;
            $status = 'success';
            $message = "User created successfully.";
        }else
        {
            $code = Response::HTTP_BAD_REQUEST;
            $status = 'failure';
            $message = 'Email or username already exists.';
        }

        return new JsonResponse([
            'status' => $status,
            'code' => $code,
            'message' => $message
        ], Response::HTTP_CREATED);
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
