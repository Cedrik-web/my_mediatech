<?php

namespace App\Controller;

use App\Entity\Network;
use App\Entity\User;
use App\Form\NetworkType;
use App\Repository\GroupRepository;
use App\Repository\NetworkRepository;
use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Constraint\IsEmpty;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/network')]
class NetworkController extends AbstractController
{
    #[Route('/', name: 'app_network_index', methods: ['GET'])]
    public function index( UserRepository $userRepository): Response
    {   

        return $this->render('network/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_network_new', methods: ['GET', 'POST'])]
    public function new($id, Request $request, NetworkRepository $networkRepository, UserRepository $userRepository): Response
    {
        $follow_id = (int) explode('-', $id, 2)[0];

        // init de l'utilisateur
        $user = $userRepository->find($this->getUser());
        // init de l'utilisateur ajouter en amis
        $user_selected = $userRepository->find($follow_id);

        // créer un tableau id de l'utilisateur demandé en amis pour l'ajout d'amis
        $array2 = $user_selected->getId();

        // enregistre la liste d'amis de utilisateur
        $friends = $user->getMyfriends();
        // enregistre la liste de followers de utilisateur en demande d'amis
        $followers = $user_selected->getMyFollowers();
        
        // redefinie la liste des amis de l'utilisateur
        array_push($friends, $array2 );
        $user->setMyfriends($friends);

        // enregistre en BDD les modifications porté aux utilisateurs
        $userRepository->save($user, true);

        if ($followers == []) {

            // créer un tableaux avec comme clé id de l'utilisateur et en valeur 0 pour la demande d'amis
            $array = [$user->getId()=> 0];

            // redéfinie la liste des followers le l'utitisateur en demande d'amis
            array_push($followers, $array);
            $user_selected->setMyFollowers($followers);

            // enregistre en BDD les modifications porté aux utilisateurs
            $userRepository->save($user_selected, true);

            return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER); 

        } else {

            if (array_key_exists($user->getId(), $followers[0])) {

                return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER); 
            
            } else {
                // créer un tableaux avec comme clé id de l'utilisateur et en valeur 0 pour la demande d'amis
                $array = [$user->getId()=> 0];

                // redéfinie la liste des followers le l'utitisateur amis
                array_push($followers, $array);
                $user_selected->setMyFollowers($followers);

                // enregistre en BDD les modifications porté aux utilisateurs
                $userRepository->save($user_selected, true);
    
                return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER); 
            }  
        }
    }

    #[Route('/again/{id}', name: 'app_network_again', methods: ['GET', 'POST'])]
    public function again($id, UserRepository $userRepository): Response
    {
        $follow_id = (int) explode('-', $id, 2)[0];
    
        // init de l'utilisateur
        $user = $userRepository->find($this->getUser());
        // init de l'utilisateur ajouter en amis
        $user_selected = $userRepository->find($follow_id);

        // enregistre la liste de followers de utilisateur en demande d'amis
        $followers = $user_selected->getMyFollowers();
        
        // créer un tableaux avec comme clé id de l'utilisateur et en valeur 0 pour la demande d'amis
        $array = [$user->getId()=> 0];

        // redéfinie la liste des followers le l'utitisateur en demande d'amis
        array_push($followers, $array);
        $user_selected->setMyFollowers($followers);
        
        // enregistre en BDD les modifications porté aux utilisateurs
        $userRepository->save($user_selected, true);

        return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER); 
    }

    #[Route('/{id}', name: 'app_network_show', methods: ['GET'])]
    public function show(Network $network): Response
    {
        return $this->render('network/show.html.twig', [
            'network' => $network,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_network_edit', methods: ['GET'])]
    public function edit($id, UserRepository $userRepository): Response
    {   
        // recupere l'id du slug de utilisateur auquel on valide la demande d'ami
        $follow_id = (int) explode('-', $id, 2)[0];

        // init de l'utilisateur
        $user = $userRepository->find($this->getUser());

        // recupere tous les followers de l'utilisateur
        $followers = $user->getMyFollowers()[0];
        
        if (array_key_exists($follow_id, $followers)) {

            
            if ($followers[$follow_id] == 1) {
              
                unset($followers[$follow_id]);
                $user->setMyFollowers($followers);
                $userRepository->save($user, true);
        
                return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER);
            } else {

                $followers[$follow_id] = 1;
                $user->setMyFollowers([$followers]);
                $userRepository->save($user, true);
        
                return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER);
            }

        } else {
  
            $followers[$follow_id] = 0;
            $user->setMyFollowers([$followers]);
            $userRepository->save($user, true);
    
            return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER);
        } 
    }

    #[Route('/{id}', name: 'app_network_delete', methods: ['POST'])]
    public function delete($id, UserRepository $userRepository): Response
    {   
        $follow_id = (int) explode('-', $id, 2)[0];

        // init de l'utilisateur
        $user = $userRepository->find($this->getUser());

        $friends = $user->getMyfriends();
        unset($friends[array_search($follow_id, $friends)]);
      
        $user->setMyfriends($friends);
        $userRepository->save($user, true);

        return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER);
    }
}
