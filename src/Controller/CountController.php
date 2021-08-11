<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use App\Repository\RestoRepository;
use App\Repository\TablesRepository;
use App\Repository\CommandeRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CountController extends AbstractController
{
    private $tokenStorage;
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    /**
     * @Route("/api/count", name="count")
     */
    public function count(
        SerializerInterface $serializer,
        MenuRepository $menuRepository,
        CommandeRepository $commandeRepository,
        ReservationRepository $reservationRepository,
        TablesRepository $tablesRepository, 
        RestoRepository $restoRepository
        ): Response
        {
        $userConnecte = $this->tokenStorage->getToken()->getUser();
        $resto = $restoRepository->findOneBy(["user" => $userConnecte]);
        $menu = $menuRepository->findby(["user" => $userConnecte]);
        $table = $tablesRepository->findby(["user" => $userConnecte]);
        // liste des commandes par resto 
        $cmd = $commandeRepository->findAll();
        $dataCommandes = [];
        $dayCmd = ["Monday"=> 0,"Tuesday"=> 0,"Wednesday"=> 0,"Thursday"=> 0,"Friday"=> 0,"Saturday"=> 0,"Sunday" => 0];
        foreach ($cmd as $key => $commandes) {
            foreach ($commandes->getPlatCommandes() as $value) {
                if($value->getPlat()->getUser()->getId() === $resto->getUser()->getId()) {
                    $dataCommandes [] = $commandes;
                }
            }
            
           
            $days = ["Monday"=> 0,"Tuesday"=> 0,"Wednesday"=> 0,"Thursday"=> 0,"Friday"=> 0,"Saturday"=> 0,"Sunday" => 0];
            if ($dataCommandes) {

                if($value->getPlat()->getUser()->getId() === $resto->getUser()->getId()) {
                    $dayCmd = [] ;
                    $date = $commandes->getCreatedAt()->format('Y-m-d');
                    $dayOfWeek[] = date('l', strtotime( $date));
                    foreach (array_count_values($dayOfWeek) as $key => $value) {
                        $dayCmd[$key] = $value;
                    }
                    
                    foreach ($days as $key => $value) {
                        if (array_key_exists($key, $dayCmd) === false) {
                            $new = [
                                $key  => 0,
                            ];
                            $dayCmd = (array_merge($dayCmd,$new));
                        }
                    }
                }
            } else {
                $dayCmd = ["Monday"=> 0,"Tuesday"=> 0,"Wednesday"=> 0,"Thursday"=> 0,"Friday"=> 0,"Saturday"=> 0,"Sunday" => 0];
            }
            
        }
        $commande = [];
        foreach (array_unique($dataCommandes, SORT_REGULAR) as  $value) {
            $commande [] = $value;
        }
        // liste des reservations par resto 
        $data = $reservationRepository->findAll();
        $dataReservations = [];
        foreach ($data as $key => $reservation) {
            foreach ($reservation->getTables() as $key => $value) {
                if($value->getResto()->getId() === $resto->getId()) {
                    $dataReservations [] = $reservation;
                }
            }
            
        }
        $reservation = [];
        if ($dataReservations) {
            foreach (array_unique($dataReservations, SORT_REGULAR) as  $value) {
                $reservation [] = $value;
                $dates = $value->getCreatedAt()->format('Y-m-d');
                $dayOfWeeks[] = date('l', strtotime( $dates));
                $dayReserv = [] ;
                foreach (array_count_values($dayOfWeeks) as $key => $value) {
                    $dayReserv[$key] = $value;
                }
                $days = ["Monday"=> 0,"Tuesday"=> 0,"Wednesday"=> 0,"Thursday"=> 0,"Friday"=> 0,"Saturday"=> 0,"Sunday" => 0];
                foreach ($days as $key => $value) {
                    if (array_key_exists($key, $dayReserv) === false) {
                        $new = [
                            $key  => 0,
                        ];
                        $dayReserv = (array_merge($dayReserv,$new));
                    }
                }
            }
        } else {
            $dayReserv = ["Monday"=> 0,"Tuesday"=> 0,"Wednesday"=> 0,"Thursday"=> 0,"Friday"=> 0,"Saturday"=> 0,"Sunday" => 0];
        }
        $data = [
            "commande" => count($commande),
            "reservation" => count($reservation),
            "menu" => count($menu),
            "table" => count($table),
            "dayOfCmd" => $dayCmd,
            "dayOfReserv" => $dayReserv
        ];
        $res = $serializer->serialize($data, 'json');
        return new Response($res, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
