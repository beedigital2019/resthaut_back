<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Tables;
use App\Entity\Reservation;
use App\Repository\RoleRepository;
use App\Repository\RestoRepository;
use App\Repository\TablesRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ReservationController extends AbstractController
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        
    }
    /**
     * @Route("/api/add/reservation", name="reservation")
     */
    public function addReservationByClient(Request $request, TablesRepository $tablesRepository ,EntityManagerInterface $manager ,RoleRepository $roleRepository, RestoRepository $restoRepository): Response
    {
        $values = json_decode($request->getContent());
        $reservation = new Reservation();
        $user = $this->tokenStorage->getToken()->getUser(); 
        $table = $tablesRepository->findBy(["id" => $values->tables]);
        // foreach ($table as  $value) {
        //     # code...
        //     $table = $serializer->deserialize($value, Tables::class, 'json');
        // }
        //dd($table);
        $reservation->setCreatedAt(\DateTime::createFromFormat('Y-m-d', $values->createdAt))
                    ->setHeure(\DateTime::createFromFormat('H:m', $values->heure))
                    ->setUser($user);
        foreach ($table as  $value) {
            $reservation->addTable($value);
        }
        $manager->persist($reservation);
        $manager->flush();
        $data = [
            'status' => 201,
            'message' => 'Votre réservation a été bien enrégistré. '];

        return new JsonResponse($data, 201);
    }
    
    /**
     * @Route("/api/list/reservation", name="list_reserv")
     */
    public function list(ReservationRepository $reservationRepository, TablesRepository $tablesRepository ,SerializerInterface $serializer, RestoRepository $restoRepository)
    {
        $userConnecte = $this->tokenStorage->getToken()->getUser();
        $resto = $restoRepository->findBy(["user" => $userConnecte]);
        $tables = $tablesRepository->findBy(["resto" => $resto["0"]]);
        
        $new_array = [];
        foreach($tables as $key => $value) {
            $new_array[] = $value->getReservation();
        }
        if($tables){
            
            $dataTable = $serializer->serialize($new_array["0"], 'json');

            return new Response($dataTable, 200, [
                'Content-Type' => 'application/json'
            ]);
        } else {
            $data = [

                'status' => 204,
                'message' => 'Pas de reservation. '
            ];
            return new JsonResponse($data, 204);
        }
       
    }
}
