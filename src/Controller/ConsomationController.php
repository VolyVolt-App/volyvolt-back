<?php

namespace App\Controller;

use Carbon\Carbon;
use App\Entity\Consomation;
use App\Entity\ConsomationPredit;
use App\Repository\ClientRepository;
use App\Repository\AppareilRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConsomationRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ConsomationPreditRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConsomationController extends AbstractController
{
    private $clientRepository;
    private $appareilRepository;
    private $consomationPreditRepository;
    private $consomationRepository;
    private $em;


    public function __construct(ClientRepository $clientRepository, AppareilRepository $appareilRepository,ConsomationPreditRepository $consomationPreditRepository,ConsomationRepository $consomationRepository , EntityManagerInterface $em)
    {
       $this->clientRepository= $clientRepository;
       $this->appareilRepository= $appareilRepository;
       $this->consomationPreditRepository=$consomationPreditRepository;
       $this->consomationRepository=$consomationRepository;
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

    #[Route('/newConsomation', name: 'app_new_consomation', methods:'POST')]
    public function newConsomation(Request $request): JsonResponse
    {
        //consomation clientId appareilId

        $consomation= new Consomation();

        $year = $request->request->get('year');
        $month = $request->request->get('month');
        $day = $request->request->get('day');

        $date=Carbon::create($year, $month, $day, 0, 0, 0);

        $consomation->setConsomation($request->request->get('consomation'));
        $consomation->setDate($date);

       // $client = $this->clientRepository->findOneBy
        $client= $this->clientRepository->findOneById($request->request->get('clientId'));
        $consomation->setClientId($client);

        $consomationPredit = $this->consomationPreditRepository->findOneBy([
            'client'=> $client, 
            'startWeek' => $date->startOfWeek(),
        ]);

        //dd($consomationPredit);
        $consomationPredit->setConsomationReel(true);
        $consomation->setConsomationPredit($consomationPredit);

        $appareil=$this->appareilRepository->findOneById($request->request->get('appareilId'));
        $consomation->setAppareilId($appareil);

        //dd($consomation);

        $this->em->getConnection()->beginTransaction();
        try {

            $this->em->persist($consomation);
            $this->em->persist($consomationPredit);

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

    #[Route('/newConsomationPredit', name: 'app_new_consomation_predit')]
    public function newConsomationPredit(Request $request): JsonResponse
    {

        $consomationPredit = new ConsomationPredit();
        //$now=Carbon::now();

        $year = $request->request->get('year');
        $month = $request->request->get('month');
        $day = $request->request->get('day');

        $consomationPredit->setConsomation($request->request->get('consomation'));

        //$startWeek= $now->startOfWeek();
        //$endWeek=$now->endOfWeek();
        //$date=Carbon::createFromDate(2023,9,30,'Europe/Berlin');
        $date=Carbon::create($year, $month, $day, 0, 0, 0);
        
        $consomationPredit->setStartWeek($date->startOfWeek());
        $consomationPredit->setConsomationReel(false);

        //tsy maints atao anatin io fa misy conflit start sy ny end
        $startWeek=$date->startOfWeek()->toDateTimeString();
        
        

        $consomationPredit->setStartWeek(new \DateTime($startWeek));
        $consomationPredit->setEndWeek($date->endOfWeek());



        $client= $this->clientRepository->findOneById($request->request->get('clientId'));
        //dd($client);
        $consomationPredit->setClient($client);

        //dd($date->endOfWeek(Carbon::SUNDAY));
        //dd($consomationPredit);

        
        $this->em->getConnection()->beginTransaction();
        try {

            $this->em->persist($consomationPredit);

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

    #[Route('/getConsomationPredit/{id}', name: 'app_get_consomation_predit_by_user')]
    public function getConsomationPreditbyUser($id): JsonResponse
    {

        $consomationPredit=$this->consomationPreditRepository->findConsomationPredictedByClient($id,false);
        $consomation = array();
        $labelConsomation = array();

        foreach ($consomationPredit as $key => $cPredit) {
            $consomation[$key] = $cPredit->getConsomation();
            $labelConsomation[$key] = Carbon::instance($cPredit->getStartWeek())->locale('fr_FR')->isoFormat('MMM Do ');//.' - '.Carbon::instance($cPredit->getEndWeek())->locale('fr_FR')->isoFormat('MMM Do ');
        }


        return $this->json(['consomation'=>$consomation, 'label'=>$labelConsomation]);
    }

    #[Route('/getConsomationReelandPredit/{id}', name: 'app_get_reel_and_predit_consomation_by_user')]
    public function getConsomationReelAndPreditbyUser($id): JsonResponse
    {
        //$client = $this->clientRepository->findOneById($id);

        $_consomationPredit=$this->consomationPreditRepository->findConsomationPredictedByClient($id,true);
        $consomationPredit = array();
        $labelConsomation = array();
        $consomation = array();

        //dd($_consomationPredit);

        foreach ($_consomationPredit as $key => $cPredit) {
            $consomationPredit[$key] = $cPredit->getConsomation();
            $labelConsomation[$key] = Carbon::instance($cPredit->getStartWeek())->locale('fr_FR')->isoFormat('MMM Do ');//.' - '.Carbon::instance($cPredit->getEndWeek())->locale('fr_FR')->isoFormat('MMM Do ');
            //dd($cPredit);
           // $_consomation = $this->consomation()
            $_consomation = $this->consomationRepository->findByConsomationPredit($cPredit);

            $dataConsomation=0;
            $i=0;
            foreach ($_consomation as $cons ){
                $dataConsomation = $dataConsomation+$cons->getConsomation();
                $i++;
            }
            $consomation[$key]=$dataConsomation/$i;
        }


        return $this->json(['consomationPredit'=>$consomationPredit,'consomation'=>$consomation, 'label'=>$labelConsomation]);
    }

    #[Route('/getConsomation/{id}', name: 'app_get_consomation_by_user')]
    public function getConsomationbyUser($id): JsonResponse
    {
        $_consomation = $this->consomationRepository->findLastConsomationByUser($id);
        //dd($consomation);

        $consomation = array();

        foreach ($_consomation as $key => $cons){
            $consomation [$key]['id'] = $cons->getId();
            $consomation[$key]['appareilId']= $cons->getAppareilId()->getAppareilId();
            $consomation[$key]['consomation']= $cons->getConsomation();
            $consomation[$key]['date']= Carbon::instance($cons->getDate())->locale('fr_FR')->isoFormat('lll');
            //$consomation[$key]['']= $cons->get;

        }
        return $this->json($consomation);
    }

    #[Route('/consomationfromrasp', name: 'app_post_form_rasp')]
    public function getConsomationfromrasp(Request $request): JsonResponse
    {
                //set VersoPhotoCIN
                $file = $request->files->get('file');
                //dd($file);
                $filename = md5(uniqid()) . '.' . $file->guessClientExtension();
                $path = $this->getParameter('kernel.project_dir') . '/public/consomation';
                $file->move($path, $filename);

                $data = array();
               // $dataSingle = array();
                $i=0;

             /*  // dd($this->getParameter('kernel.project_dir').'\public\consomation');
                if ($file instanceof UploadedFile) {
                    $raw = file_get_contents($this->getParameter('kernel.project_dir')."\public\consomation\\".$filename);
                    //dd($raw);
                    $data = explode('\n',$raw);
                    dd($data);
                    }
                 dd('tsy mety');   

                //dd($file->readfile());*/

                $fh = fopen($this->getParameter('kernel.project_dir')."\public\consomation\\".$filename,'r');
                    while ($line = fgets($fh)) {
                    // <... Do your work with the line ...>
                   // dd($line);
                    $data[$i]=$line;
                    $i=$i+1;
                    }
                fclose($fh);

               // dump($data);

                $dataClient = explode(';',$data[0]);

               // dd($dataClient);
                //getclientID
                $client = $this->clientRepository->findOneByClientId($dataClient);
                  //  dd($client);
                //getAppareilID
                $dataAppareil= explode(';',$data[2]); 
                $appareil = $this->appareilRepository->findOneByAppareilId($dataAppareil[0]);
               // dd($appareil);
                //send two

    //new consomation

                for ($i=6; $i<13; $i++){

                
                $dataConsomation= explode(';',$data[2]);
                $date=$dataConsomation[4];
                $hm=$dataConsomation[3];

                $Uvolt=$dataConsomation[1];
                $Iampere = $dataConsomation[2];

                $datetime=Carbon::createFromFormat('Y:m:d H:i', $date.' '.$hm);

                // add first data consomation
                $consomation = new Consomation();
                
                //date
                $datetimeString=$datetime->toDateTimeString();
                $consomation->setDate(new \DateTime($datetimeString));

                // $client = $this->clientRepository->findOneBy

                 $consomation->setClientId($client);
                 $consomation->setAppareilId($appareil);

                 $consomation->setConsomation($Uvolt*$Iampere+300);
                 //$consomation1->setConsomation($Uvolt*$Iampere);
         
                 $consomationPredit = $this->consomationPreditRepository->findOneBy([
                     'client'=> $client,
                     'startWeek' => $datetime->startOfWeek(),
                 ]);

                 if($consomationPredit){
                    $consomationPredit->setConsomationReel(true);
                    $consomation->setConsomationPredit($consomationPredit);
                 }

                 $this->em->getConnection()->beginTransaction();
                try {

                    $this->em->persist($consomationPredit);

                    $this->em->flush();
                    $this->em->commit();

                } catch (\Exception $e) {

                    $this->em->rollback();
                    throw $e;
                }

                 //dump($consomation);
                }
         
    //dd($consomationPredit);
         

               // dd();


        return $this->json(['mess'=>'Ok']);
    }


}

/*

<?php
namespace App\TimeController;

use Carbon\Carbon;

class TimeController
{
    public function transformTime(Carbon $datetime){
        $now=Carbon::now();
        
        $datetime->locale('fr_FR');

        //differnce refa 2 jours
        if($datetime->diffInDays($now,false)<2){
            //difference de deux heurs 
               if($datetime->diffInHours($now,false)<2){
                $date=$datetime->diffForHumans();                
                }
                else{
                    $date=$datetime->calendar();

                }          
        } 
        else{
            $date=$datetime->isoFormat('ddd LT');
        }
  
        return $date;
    



*/
