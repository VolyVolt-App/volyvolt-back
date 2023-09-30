<?php

namespace App\Controller;

use App\Entity\Consomation;
use App\Repository\AppareilRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConsomationController extends AbstractController
{
    private $clientRepository;
    private $appareilRepository;
    private $em;


    public function __construct(ClientRepository $clientRepository, AppareilRepository $appareilRepository, EntityManagerInterface $em)
    {
       $this->clientRepository= $clientRepository;
       $this->appareilRepository= $appareilRepository;
       $this->em=$em;
    }

    #[Route('/consomation', name: 'app_consomation')]
    public function consomation(Request $request): JsonResponse
    {
        //consomation clientId appareilId

        $consomation= new Consomation();

        $consomation->setConsomation($request->request->get('consomation'));
        $consomation->setDate(new \DateTime());

       // $client = $this->clientRepository->findOneBy
        $client= $this->clientRepository->findOneById($request->request->get('clientId'));
        $consomation->setClientId($client);

        $appareil=$this->appareilRepository->findOneById($request->request->get('appareilId'));
        $consomation->setAppareilId($appareil);

        //dd($consomation);

        $this->em->getConnection()->beginTransaction();
        try {

            $this->em->persist($consomation);

            $this->em->flush();
            $this->em->commit();

        } catch (\Exception $e) {

            $this->em->rollback();
            throw $e;
        }

        return $this->json([
            'success' => 'volyvolt vous remercie',
            //'path' => 'src/Controller/ConsomationController.php',
        ]);
    }
}
