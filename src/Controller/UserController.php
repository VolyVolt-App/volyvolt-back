<?php

namespace App\Controller;

use App\Entity\Appareil;
use App\Entity\Client;
use App\Entity\Users;
use App\Repository\AppareilRepository;
use App\Repository\ClientRepository;
use App\Service\RandomW;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserController extends AbstractController
{
    private $randomW;
    private $clientRepository;
    private $em;
    private $appareilRepository;

    public function __construct(RandomW $randomW, ClientRepository $clientRepository,EntityManagerInterface $em, AppareilRepository $appareilRepository){
        $this->randomW= $randomW;
        $this->clientRepository=$clientRepository;
        $this->em=$em;
        $this->appareilRepository=$appareilRepository;
    }

    #[Route('/api/user', name: 'app_user')]
    public function index(): JsonResponse
    {
        $user=$this->getUser();

        return $this->json([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/client', name: 'app_client', methods: 'POST')]
    public function newClient(Request $request): JsonResponse
    {
        /* nom */
        $client = new Client();
        $client->setNom($request->request->get('nom'));

        $_allClients=$this->clientRepository->findAll();
        $allClientId= array();

        //for random id client
        foreach($_allClients as $key => $allClient ){
            $allClientId[$key]=$allClient->getClientId();
        }

        $client->setClientId($this->randomW->getNewWCId($allClientId));

        //dd($client);

        
        //methode hi enregistrena azy amn ni base
        $this->em->getConnection()->beginTransaction();
        try {

            $this->em->persist($client);

            $this->em->flush();
            $this->em->commit();

        } catch (\Exception $e) {

            $this->em->rollback();
            throw $e;
        }

        return $this->json([
            'clientId' => $client->getClientId(),
            'nom' => $client->getNom(),
        ]);
    }

    #[Route('/appareil', name: 'app_appareil', methods: 'POST')]
    public function newAppareil(): JsonResponse
    {
        /* nom */
        $appareil = new Appareil();
        //$appareil->setNom($request->request->get('nom'));

        $_allAppareils=$this->appareilRepository->findAll();
        $allAppareilId= array();

        //for random id client
        foreach($_allAppareils as $key => $allAppareil ){
            $allAppareilId[$key]=$allAppareil->getAppareilId();
        }

        $appareil->setAppareilId($this->randomW->getNewWAId($allAppareilId));

       // dd($appareil);

        
        //methode hi enregistrena azy amn ni base
        $this->em->getConnection()->beginTransaction();
        try {

            $this->em->persist($appareil);

            $this->em->flush();
            $this->em->commit();

        } catch (\Exception $e) {

            $this->em->rollback();
            throw $e;
        }

        return $this->json([
            'appareilId' => $appareil->getAppareilId(),
            //'nom' => $client->getNom(),
        ]);
    }

    #[Route('/matchapptoclient', name: 'app_match_appareil_to_client', methods: 'POST')]
    public function matchAppareilToClient(Request $request): JsonResponse
    {
        $client = $this->clientRepository->findOneById($request->request->get('clientId'));
        $appareil = $this->appareilRepository->findOneById($request->request->get('appareilId'));

        $appareil->setClient($client);

        //dd($appareil);
        //methode hi enregistrena azy amn ni base
        $this->em->getConnection()->beginTransaction();
        try {

            $this->em->persist($appareil);

            $this->em->flush();
            $this->em->commit();

        } catch (\Exception $e) {

            $this->em->rollback();
            throw $e;
        }

        return $this->json([
            'appareilId' => $appareil->getAppareilId(),
            'message' => 'success',
        ]);
    }

    #[Route('/register', name: 'app_register_user', methods: 'POST')]
    public function register(Request $request, UserPasswordHasherInterface $passwordhasher): JsonResponse
    {
        $plainPassword = $request->request->get('password');

        $user = new Users();

        $user->setRoles(['ADMIN']);

        //hashing pswrd
        $hashedPassword = $passwordhasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $user->setCreatedAt(new \DateTime());
        $user->setUsername($request->request->get('username'));
        $user->setEmail($request->request->get('email'));
        $user->setOrganization($request->request->get('organization'));


        //dd($user);
        $this->em->getConnection()->beginTransaction();
        try {

            $this->em->persist($user);

            $this->em->flush();
            $this->em->commit();

        } catch (\Exception $e) {

            $this->em->rollback();
            throw $e;
        }

        return $this->json([
            //'appareilId' => $appareil->getAppareilId(),
            'register' => 'success',
        ]);
    }


}
