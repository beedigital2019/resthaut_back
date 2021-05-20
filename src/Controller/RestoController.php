<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Resto;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\RestoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RestoController extends AbstractController
{
    
    /**
     * @Route("/api/resto/add", name="add_resto", methods={"POST"})
     */
    public function add(Request $request, USerRepository $userRepository ,EntityManagerInterface $manager,  UserPasswordEncoderInterface $passwordEncode,RoleRepository $roleRepository)
    {
        header('Content-Type: application/json; charset=utf-8');
        header("Access-Control-Allow-Methods:  POST");
        // récuprére le role du resto
        $role = $roleRepository->findOneBy(array('libelle' => 'ROLE_GERANT'));
        // Ajout utilisateur du resto
        $user = new User();
        $username = $request->request->all()["username"];
        $password = $request->request->all()["password"];
        $nomComplet = $request->request->all()["nomComplet"];
        $telephone = $request->request->all()["telephone"];
        $usernameExiste = $userRepository->findOneBy(array("username" =>$username));
        if($usernameExiste == true){
            $data = [

                'status' => 500,
                'message' => 'Cette username a été déja utilisé. '
            ];
    
            return new JsonResponse($data, 500);
        }
        $user->setPassword($passwordEncode->encodePassword($user, $password))
                ->setRole($role)
                ->setUsername($username)
                ->setNomComplet($nomComplet)
                ->setTelephone($telephone);
        $manager->persist($user);
        // Ajout resto et imagae de profil
        $resto = new Resto();
        $nomResto = $request->request->all()["nomResto"];
        $description = $request->request->all()["description"];
        $adresse = $request->request->all()["adresse"];
        $image = $request->files->get("image");
        $image = fopen($image->getRealPath(),"rb");
        $resto->setNomResto($nomResto)
                ->setDescription($description)
                ->setUser($user)
                ->setImage($image)
                ->setAdresse($adresse);
        $manager->persist($resto);
        $manager ->flush();

        fclose($image);
        $data = [

            'status' => 201,
            'message' => 'Votre resto a été crée avec succes. '
        ];

        return new JsonResponse($data, 201);
        
    }
    /**
     * @Route("/api/resto/list", name="list_resto", methods={"GET"})
     */
    public function index(RestoRepository $restoRepository, SerializerInterface $serializer): Response
    {
        $data = $restoRepository->findAll();
        $images = [];
        foreach ($data as $entity) {
           // $images[$key] = base64_encode(stream_get_contents($entity->getImage()));
            $entity->setImage((base64_encode(stream_get_contents($entity->getImage()))));
        }
        
        $images = $serializer->serialize($data, 'json');
        return new Response($images, 200, [
            'Content-Type' => 'application/json'
        ]);

    }
    /**
     * @Route("/api/resto/list/{id}", name="details_resto", methods={"GET"})
     */
    public function details(RestoRepository $restoRepository, $id ,SerializerInterface $serializer): Response
    {
        $data = $restoRepository->find($id);
        $images = [];
        $data->setImage((base64_encode(stream_get_contents($data->getImage()))));
        $images = $serializer->serialize($data, 'json');
        return new Response($images, 200, [
            'Content-Type' => 'application/json'
        ]);

    }
}
