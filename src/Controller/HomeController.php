<?php

namespace App\Controller;
use App\Service\Algo;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $algo;
    
    public function __construct(Algo $algo)
    {
        $this->algo = $algo;
    }

    #[Route('/ping', name: 'app_home')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'pong',
        ]);
    }

    #[Route('/algo', name: 'app_algo')]
    public function algo(): JsonResponse
    {
        $feno=$this->algo->add(56);
        dd($feno);
        return $this->json([
            'message' => 'pong',
        ]);
    }
}