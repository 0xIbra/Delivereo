<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Utils\FlashMessage;
use App\Utils\JSON;
use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManagerInterface;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Stripe\Account;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/dashboard", name="admin_dashboard")
     */
    public function dashboard(ObjectManager $om)
    {
        $totalUsers = intval($om->getRepository(User::class)->countConsumers()[1]);
        $collectedFees = intval($om->getRepository(Order::class)->countOrders()[1]) * 0.50;
        $totalRestaurants = intval($om->getRepository(Restaurant::class)->countRestaurants()[1]);

        $recentUsers = $om->getRepository(User::class)->recentUsers(5);
        $restaurantApplications = $om->getRepository(Restaurant::class)->getApplications(5);

        return $this->render('admin/dashboard.html.twig', [
            'data' => [
                'totalUsers' => $totalUsers,
                'collectedFees' => $collectedFees,
                'totalRestaurants' => $totalRestaurants,
                'traffic' => 123034,
                'recentUsers' => $recentUsers,
                'restaurantApplications' => $restaurantApplications
            ]
        ]);
    }


    /**
     * @Route("/admin/dashboard/data", name="admin_dashboard_data")
     */
    public function dashboardJson(Request $request, ObjectManager $om, SerializerInterface $serializer, FlashBagInterface $flashBag)
    {
        $chartData = [];

        $end = new DateTime('+1 day');
        $end->setTime(0,0,2);
        $period = new DatePeriod(
            new DateTime('1 week ago'),
            new DateInterval('P1D'),
            $end
        );

        foreach ($period as $date)
        {
            $chartData['dates'][] = $date->format('d/m/Y');
            $orders = $om->getRepository(Order::class)->countByDate($date)[1];
            $restaurants = $om->getRepository(Restaurant::class)->countByDate($date)[1];
            $users = $om->getRepository(User::class)->countByDate($date)[1];
            $chartData['data']['collectedFees'][] = 0.50 * intval($orders);
            $chartData['data']['restaurants'][] = $restaurants;
            $chartData['data']['users'][] = $users;
        }


//        return $this->render('dump.html.twig', ['dump' => $chartData]);

        return JSON::JSONResponse([
            'status' => true,
            'data' => $chartData
        ], 200, $serializer);
    }


    /**
     * @Route("/admin/users", name="admin_users")
     * @param Request $request
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function users(Request $request, ObjectManager $om)
    {
        $users = null;
        if ($request->query->has('users'))
        {
            $users = $om->getRepository(User::class)->getConsumersAndOwners();
        }else
        {
            $users = $om->getRepository(User::class)->getConsumersAndOwners(20);
        }

        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/admin/restaurants", name="admin_restaurants")
     */
    public function restaurants(Request $request, ObjectManager $om)
    {
        $restaurants = null;
        if ($request->query->has('restaurants'))
        {
            $restaurants = $om->getRepository(Restaurant::class)->findAll();
        }else
        {
            $restaurants = $om->getRepository(Restaurant::class)->findBy([], ['id' => 'DESC'], 20);
        }

        return $this->render('admin/restaurants.html.twig', [
            'restaurants' => $restaurants
        ]);
    }


    /**
     * @Route("/admin/orders", name="admin_orders")
     */
    public function orders(Request $request, ObjectManager $om)
    {
        $orders = null;
        if ($request->query->has('orders'))
        {
            $orders = $om->getRepository(Order::class)->findAll();
        }else
        {
            $orders = $om->getRepository(Order::class)->findBy([], ['id' => 'DESC'], 20);
        }

        return $this->render('admin/orders.html.twig', [
            'orders' => $orders
        ]);
    }


    /**
     * @Route("/admin/users/{user}", name="visit_user", requirements={"user"="\d+"})
     * @param User|null $user
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function visitUser(User $user = null, ObjectManager $om)
    {
        if (!$this->isGranted('visit', $user))
        {
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/visitUser.html.twig', [
            'user' => $user
        ]);
    }


    /**
     * @Route("/admin/user/{user}/deactivate", name="admin_deactivate_user", requirements={"user"="\d+"})
     */
    public function deactivateUser(User $user, ObjectManager $om, FlashBagInterface $flashBag)
    {
        $user->setEnabled(false);
        $om->persist($user);
        $om->flush();
        FlashMessage::message($flashBag, 'success', 'Utilisateur #'. $user->getId(). ' desactivé');
        return $this->redirectToRoute('admin_users');
    }


    /**
     * @Route("/admin/user/{user}/activate", name="admin_activate_user", requirements={"user"="\d+"})
     */
    public function activateUser(User $user, ObjectManager $om, FlashBagInterface $flashBag)
    {
        $user->setEnabled(true);
        $om->persist($user);
        $om->flush();
        FlashMessage::message($flashBag, 'success', 'Utilisateur #'. $user->getId(). ' activé');
        return $this->redirectToRoute('admin_users');
    }


    /**
     * @Route("/admin/users/{user}/delete", name="admin_delete_user", requirements={"user"="\d+"})
     */
    public function deleteUser(User $user, UserManagerInterface $manager, FlashBagInterface $flashBag)
    {
        FlashMessage::message($flashBag, 'success', 'Utilisateur #'. $user->getId() .' a été supprimé');
        $manager->deleteUser($user);
        return $this->redirectToRoute('admin_users');
    }





    /**
     * @Route("admin/restaurants/{restaurant}", name="visit_restaurant", requirements={"restaurant"="\d+"})
     */
    public function visitRestaurant(Restaurant $restaurant = null, ObjectManager $om)
    {
        if ($restaurant === null)
        {
            FlashMessage::message($this->get('session')->getFlashBag(), 'danger', 'Restaurant non trouvé');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/visitRestaurant.html.twig', [
            'restaurant' => $restaurant
        ]);
    }


    /**
     * @Route("/admin/restaurants/accept/{restaurant}", name="accept_restaurant", requirements={"restaurant"="\d+"})
     * @param Restaurant|null $restaurant
     * @param ObjectManager $om
     * @param \Swift_Mailer $mailer
     * @param FlashBagInterface $flashBag
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function acceptRestaurant(Restaurant $restaurant = null, ObjectManager $om, \Swift_Mailer $mailer, FlashBagInterface $flashBag)
    {
        if ($restaurant === null)
        {
            FlashMessage::message($flashBag, 'danger', 'Restaurant non trouvé');
            return $this->redirectToRoute('admin_dashboard');
        }

        $user = $restaurant->getOwner();
        $message = (new \Swift_Message('Demande ' . $restaurant->getName() . ' acceptée'))
            ->setFrom('delivereo.team@gmail.com')
            ->setTo($user->getEmail())
            ->setBody($this->renderView('owner/email/confirmed.html.twig', [
                'user' => $user,
                'restaurant' => $restaurant
            ]), 'text/html');
        $mailer->send($message);

        $user->removeRole('ROLE_CONSUMER');
        $user->addRole('ROLE_OWNER');
        $restaurant->setEnabled(true);
        $om->persist($user);
        $om->persist($restaurant);
        $om->flush();

        FlashMessage::message($flashBag, 'success', 'Demande acceptée avec succès');
        return $this->redirectToRoute('admin_dashboard');
    }


    /**
     * @Route("/admin/restaurants/refuse/{restaurant}", name="refuse_restaurant", requirements={"restaurant"="\d+"})
     * @param Restaurant|null $restaurant
     * @param ObjectManager $om
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function refuseRestaurant(Restaurant $restaurant = null, ObjectManager $om, \Swift_Mailer $mailer, FlashBagInterface $flashBag)
    {
        if ($restaurant === null)
        {
            FlashMessage::message($flashBag, 'danger', 'Restaurant non trouvé');
            return $this->redirectToRoute('admin_dashboard');
        }
        $user = $restaurant->getOwner();
        $message = (new \Swift_Message('Demande ' . $restaurant->getName() . ' refusée'))
            ->setFrom('delivereo.team@gmail.com')
            ->setTo($user->getEmail())
            ->setBody($this->renderView('owner/email/refused.html.twig', [
                'user' => $user,
                'restaurant' => $restaurant
            ]), 'text/html');
        $mailer->send($message);

        $user->setRestaurant(null);
        $om->persist($user);
        $om->remove($restaurant);
        $om->flush();

        FlashMessage::message($flashBag, 'success', 'Demande refusée avec succès');
        return $this->redirectToRoute('admin_dashboard');
    }

}
