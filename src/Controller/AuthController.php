<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Utils\JSON;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    private $eventDispatcher;
//    private $formFactory;
    private $userManager;
//    private $tokenStorage;


    public function __construct(EventDispatcherInterface $eventDispatcher, UserManagerInterface $userManager)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->userManager = $userManager;
    }


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
     * @Route("/api/register", name="json_registration", methods={"POST"})
     *
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param UserManagerInterface $userManager
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function register(Request $request, ValidatorInterface $validator, UserManagerInterface $userManager, SerializerInterface $serializer, UserPasswordEncoderInterface $encoder)
    {
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
            $form = $this->createForm(UserFormType::class, $user);
            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

            $user->setPlainPassword($user->getPassword());
            $userManager->updateUser($user);
            $code = Response::HTTP_CREATED;
            $status = 'success';
            $message = "Utilisateur a été crée. Merci d'activer votre compte à partir de votre boîte mail.";
        }else
        {
            $code = Response::HTTP_BAD_REQUEST;
            $status = 'failure';
            $message = "Email ou nom d'utilisateur existe déjà.";
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

        $user->setRoles(['ROLE_CONSUMER']);
        $user->setEnabled(false);
        $userManager->updateUser($user);
        return true;
    }


    /**
     * @Route("/api/registration/confirm/{token}", name="confirmRegistration", methods={"GET"})
     *
     * @param Request $request
     * @param $token
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function confirmRegistration($token, Request $request,  ObjectManager $om, SerializerInterface $serializer)
    {
        $userManager = $this->userManager;

        $user = $userManager->findUserByConfirmationToken($token);
        if ($user === null)
        {
            return JSON::JSONResponse([
                'message' => sprintf('Aucun utilisateur n\'a été trouvée avec le token "%s"', $token),
                'status' => false
            ], Response::HTTP_NOT_FOUND, $serializer);
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user);

        return $this->redirect(getenv('FRONT_END_LOGIN'));
    }

}
