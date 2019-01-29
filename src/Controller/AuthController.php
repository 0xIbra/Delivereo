<?php

namespace App\Controller;

use App\Entity\Gender;
use App\Entity\Image;
use App\Entity\User;
use App\Form\UserFormType;
use App\Uploader\Uploader;
use App\Utils\JSON;
use App\Utils\Validation;
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
     * @Route("/api/auth/owner/restaurant", name="myRestaurant", methods={"GET"})
     *
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function myRestaurant(SerializerInterface $serializer)
    {
        $restaurant = $this->getUser()->getRestaurant();
        if (!$this->isGranted('edit', $restaurant))
        {
            return JSON::JSONResponse([
                'message' => 'Vous n\'avez pas les droits pour acceder a cette page.',
                'status' => false
            ], Response::HTTP_UNAUTHORIZED, $serializer);
        }

        return JSON::JSONResponseWithGroups($restaurant, Response::HTTP_OK, $serializer, [
            'front',
            'customer',
            'owner'
        ]);
    }


    /**
     * @Route("/api/auth/me/edit", name="editUserJson", methods={"PUT"})
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param UserManagerInterface $userManager
     * @param ObjectManager $om
     * @return Response
     */
    public function editUser(Request $request, SerializerInterface $serializer, UserManagerInterface $userManager, ObjectManager $om)
    {
        $data = $serializer->deserialize($request->getContent(), User::class, 'json');
        $exists = $userManager->findUserByUsername($data->getUsername());
        if ($exists !== null)
        {
            if ($exists->getId() !== $data->getId())
            {
                return JSON::JSONResponse([
                    'message' => 'Ce nom d\'utilisateur existe déjà.',
                    'status' => false,
                    'existing' => $exists->getId(),
                    'current' => $data->getId()
                ], Response::HTTP_BAD_REQUEST, $serializer);
            }
        }
        $user = $this->getUser();
        $user->setUsername($data->getUsername());
        $user->setFirstName($data->getFirstName());
        $user->setLastName($data->getLastName());
        if ($data->getGender() !== null)
        {
            $gender = $om->getRepository(Gender::class)->find($data->getGender()->getId());
            $user->setGender($gender);
        }
        $userManager->updateUser($user);
        return JSON::JSONResponse([
            'message' => 'Vos données ont été mises à jour.',
            'status' => true
        ], Response::HTTP_ACCEPTED, $serializer);
    }


    /**
     * @Route("/api/auth/image/add", name="addImageJson", methods={"POST"})
     *
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function addImageJson(Request $request, ValidatorInterface $validator, ObjectManager $om, SerializerInterface $serializer)
    {
        if (!$request->files->has('image'))
        {
            return JSON::JSONResponse([
                'message' => "Merci d'inclure l'image.",
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }
        $user = $this->getUser();
        $file = $request->files->get('image');
        $image = new Image();
        $image->setTitle($user->getUsername() . '_' .md5(uniqid()));
        $image->setImage($file);
        $validation = Validation::validateforJson($validator, $image);
        if (!$validation['validation'])
        {
            return JSON::JSONResponse([
                'message' => $validation['messages']
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }else
        {
            $upload = Uploader::upload($file, ['public_id' => $image->getTitle(), 'angle' => 0, 'width' => 512]);
            $image->setUrl($upload['secure_url']);
            $user->setImage($image);
            $om->persist($user);
            $om->flush();
            $this->get('session')->getFlashBag()->add('success', 'Image ajoutée.');
            return JSON::JSONResponse([
                'message' => 'Image ajoutée.',
                'status' => true
            ], Response::HTTP_ACCEPTED, $serializer);
        }
    }


    /**
     * @Route("/api/auth/image/delete", name="deleteImageJson", methods={"DELETE"})
     *
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function deleteImage(ObjectManager $om, SerializerInterface $serializer)
    {
        $user = $this->getUser();
        if ($user->getImage() === null)
        {
            return JSON::JSONResponse([
                'message' => "Vous n'avez pas d'image pour supprimer.",
                'status' => false
            ], Response::HTTP_BAD_REQUEST, $serializer);
        }
        $om->remove($user->getImage());
        $user->setImage(null);
        $om->persist($user);
        $om->flush();
        return JSON::JSONResponse([
            'message' => 'Image supprimée.',
            'status' => true
        ], Response::HTTP_ACCEPTED, $serializer);
    }


    /**
     * @Route("/api/auth/password/change", name="changePasswordJson", methods={"PUT"})
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param UserManagerInterface $userManager
     * @return Response
     */
    public function changePasswordJson(Request $request, SerializerInterface $serializer, UserManagerInterface $userManager, UserPasswordEncoderInterface $encoder)
    {
        if (!$request->request->has('newPassword') || !$request->request->has('currentPassword'))
        {
            return JSON::JSONResponse(
                [
                    'message' => 'Merci de fournir le mot de passe actuel avec le nouveau mot de passe.'
                ],
                Response::HTTP_NOT_FOUND,
                $serializer
            );
        }
        $currentPass = $request->request->get('currentPassword');
        $pass = $request->request->get('newPassword');
        $user = $this->getUser();

        if (!$encoder->isPasswordValid($user, $currentPass))
        {
            return JSON::JSONResponse([
                'message' => "Le mot de passe actuel n'est pas correct. "
            ], Response::HTTP_UNAUTHORIZED, $serializer);
        }

        $user->setPlainPassword($pass);
        $userManager->updateUser($user);
        return JSON::JSONResponse([
            'message' => 'Le nouveau mot de passe a été enregistré.'
        ], Response::HTTP_ACCEPTED, $serializer);
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
                'status' => false,
                'message' => 'Vous n\'êtes pas connecté'
            ], Response::HTTP_UNAUTHORIZED, $serializer);
        }
    }

    /**
     * @param SerializerInterface $serializer
     * @return Response
     *
     * @Route("/api/auth/me", name="get_user", methods={"GET"})
     */
    public function me(SerializerInterface $serializer)
    {
        return JSON::JSONResponseWithGroups($this->getUser(), Response::HTTP_OK, $serializer, ['front', 'owner']);
    }

    /**
     * @Route("/api/register", name="json_registration", methods={"POST"})
     *
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param UserManagerInterface $userManager
     * @param SerializerInterface $serializer
     * @return JsonResponse
     * @throws \Exception
     */
    public function register(Request $request, ValidatorInterface $validator, ObjectManager $om, UserManagerInterface $userManager, SerializerInterface $serializer)
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $gender = $om->getRepository(Gender::class)->find($user->getGender()->getId());
        if ($gender !== null)
        {
            $user->setGender($gender);
        } else
        {
            $user->setGender(null);
        }

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
            $user->setCreatedAt(new \DateTime());
            $userManager->updateUser($user);
            $code = Response::HTTP_CREATED;
            $status = 'success';
            $message = "Inscription réussite. Merci d'activer votre compte à partir de votre boîte mail.";
        }else
        {
            $code = Response::HTTP_NOT_FOUND;
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
