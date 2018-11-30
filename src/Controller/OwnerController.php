<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\City;
use App\Entity\Order;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Form\RestaurantFormType;
use App\Form\RestaurantModifyFormType;
use App\Utils\FlashMessage;
use App\Utils\JSON;
use App\Utils\Validation;
use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
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
     * @Route("/partner/application", name="owner_application", methods={"GET", "POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param ObjectManager $om
     * @param \Swift_Mailer $mailer
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
     * @param ObjectManager $om
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function dashboard(ObjectManager $om)
    {
        if (!$this->isGranted('edit', $this->getUser()->getRestaurant()))
        {
            FlashMessage::message($this->get('session')->getFlashBag(), 'danger', 'Vous n\'avez pas les droits pour accéder à cette page.');
            return $this->redirectToRoute('homepage');
        }
        $restaurant = $this->getUser()->getRestaurant();
        $recentOrders = $om->getRepository(Order::class)->recentRestaurantOrders($restaurant, 5);
//        $restaurant = new Restaurant();
        $revenue = 0;
        $orders = $restaurant->getOrders();
        $totalOrders = $orders->count();
        $clients = new ArrayCollection();
        foreach ($orders as $order)
        {
            $consumer = $order->getConsumer();
            if (!$clients->contains($consumer))
            {
                $clients->add($consumer);
            }
            $revenue += $order->getTotalPrice();
        }

        $today = new DateTime();

        $weatherData['icons'] = [
            '01d' => 'wi-day-sunny',
            '02d' => 'wi-day-cloudy',
            '03d' => 'wi-cloud',
            '04d' => 'wi-cloudy',
            '09d' => 'wi-showers',
            '10d' => 'wi-rain',
            '11d' => 'wi-thunderstorm',
            '13d' => 'wi-snow',
            '50d' => 'wi-fog',
            '01n' => 'wi-day-sunny',
            '02n' => 'wi-day-cloudy',
            '03n' => 'wi-cloud',
            '04n' => 'wi-cloudy',
            '09n' => 'wi-showers',
            '10n' => 'wi-rain',
            '11n' => 'wi-thunderstorm',
            '13n' => 'wi-snow',
            '50n' => 'wi-fog'
        ];

        $weatherData['days'] = [
            "Mon" => 'Lun',
            'Tue' => 'Mar',
            'Wed' => 'Mer',
            'Thu' => 'Jeu',
            'Fri' => 'Ven',
            'Sat' => 'Sam',
            'Sun' => 'Dim'
        ];

        $weatherData['months'] = [
            1 => 'Janv',
            2 => 'Févr',
            3 => 'Mars',
            4 => 'Avr',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juill',
            8 => 'Août',
            9 => 'Sept',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Déc'
        ];

        $req = curl_init();
        curl_setopt($req, CURLOPT_URL, "https://api.openweathermap.org/data/2.5/weather?zip=". $restaurant->getCity()->getZipCode() .",fr&lang=fr&units=metric&APPID=". getenv('OPENWEATHERMAP_API_KEY'));
        curl_setopt($req, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($req);
        $weatherData['weather'] = json_decode($response, JSON_UNESCAPED_UNICODE);


//        return $this->render('dump.html.twig', ['dump' => $recentOrders]);
        return $this->render('owner/dashboard.html.twig', [
            'data' => [
                'revenue' => $revenue,
                'orders' => $totalOrders,
                'clients' => $clients->count(),
            ],
            'restaurant' => $restaurant,
            'recentOrders' => $recentOrders,
            'weatherData' => $weatherData,
            'today' => $today
        ]);
    }


    /**
     * @Route("/owner/dashboard/data", name="owner_dashboard_data")
     *
     * @param Request $request
     * @param ObjectManager $om
     * @param SerializerInterface $serializer
     * @param FlashBagInterface $flashBag
     * @return mixed
     * @throws \Exception
     */
    public function dashboardJson(Request $request, ObjectManager $om, SerializerInterface $serializer, FlashBagInterface $flashBag)
    {
        $restaurant = $this->getUser()->getRestaurant();
        if ($restaurant === null)
        {
            return JSON::JSONResponse([
                'status' => false,
                'message' => 'Aucun restaurant n\'a été trouvé'
            ], 400,$serializer);
        }

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
            $orders = $om->getRepository(Order::class)->findRestaurantOrdersByDate($restaurant, $date);
            $revenue = 0;
            $orderCount = count($orders);
            $clients = new ArrayCollection();
            foreach ($orders as $order)
            {
                $revenue += $order->getTotalPrice();
                $consumer = $order->getConsumer();
                if (!$clients->contains($consumer))
                {
                    $clients->add($consumer);
                }
            }

            $chartData['data']['revenue'][] = $revenue;
            $chartData['data']['orders'][] = $orderCount;
            $chartData['data']['clients'][] = $clients->count();
        }


//        return $this->render('dump.html.twig', ['dump' => $chartData]);

        return JSON::JSONResponse([
            'status' => true,
            'data' => $chartData
        ], 200, $serializer);
    }


    /**
     * @Route("/owner/dashboard/orders", name="owner_orders")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orders()
    {
        $restaurant = $this->getUser()->getRestaurant();

        return $this->render('owner/orders.html.twig', [
            'orders' => $restaurant->getOrders()
        ]);
    }

}
