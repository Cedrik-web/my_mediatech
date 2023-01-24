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

use function PHPUnit\Framework\isEmpty;

#[Route('/network')]
class NetworkController extends AbstractController
{
    #[Route('/', name: 'app_network_index', methods: ['GET'])]
    public function index( UserRepository $userRepository, NetworkRepository $networkRepository): Response
    {   
        $networks = $networkRepository->findAll();
        $userId = array();
        $userFriends = array();
        $userFollowers = array();

        $listeFriends = array();
        $other_usersFollowers = array();
        $totalFollowers = array();

        foreach ( $networks as $network) {

            array_push($userId, $network->getUser()->getId());
            $totalFollowers = [$network->getUser()->getId() => ""];

            if ($network->getUser() == $this->getUser()) {
                array_push($userFriends, $network->getMyFriends()[0]);
                array_push($userFollowers, $network->getMyFollowers()[0]);
            } else {
                array_push($listeFriends, [$network->getUser()->getId() => $network->getMyFollowers()]);
                $other_usersFollowers =  array_merge($listeFriends, $totalFollowers);
            }
        }

        // dd($userFriends);

        return $this->render('network/index.html.twig', [
            'users' => $userRepository->findAll(),
            'networks' => $networkRepository->findAll(),
            'userFriends' => $userFriends,
            'userId' => $userId,
            'userFollowers' => $userFollowers,
            'user_selectedFollowers' => $other_usersFollowers[0],
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
        
        $user_network = $networkRepository->find($this->getUser());
        $user_network_selected = $networkRepository->find($follow_id);

        // créer un tableaux avec comme clé id de l'utilisateur et en valeur 0 pour la demande d'amis
        $array = [$user->getId()=> 0];
       
        // créer un tableau id de l'utilisateur demandé en amis pour l'ajout d'amis
        $array2 = [$user_selected->getId()];

        if ( isEmpty($user_network)) {

            if (isEmpty($user_network_selected)) {
                $network = new Network();
                $network_follower = new Network();

                $network->setUser($user);
                $network_follower->setUser($user_selected);
                
                $network->setCreatedAt(new DateTimeImmutable());
                $network_follower->setCreatedAt(new DateTimeImmutable());
    
                $network->setUpdatedAt(new DateTimeImmutable());
                $network_follower->setUpdatedAt(new DateTimeImmutable());
    
                $network_follower->setMyFollowers([$array]);
    
                $network->setMyFriends($array2);
    
                $networkRepository->save($network, true);
                $networkRepository->save($network_follower, true);
    
                return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER);

            } else {

                $network = new Network();

                // enregistre la liste de followers de utilisateur en demande d'amis
                $followers = $user_network_selected->getMyFollowers();
        
                // créer un tableaux avec comme clé id de l'utilisateur et en valeur 0 pour la demande d'amis
                $array = [$user->getId()=> 0];

                if ($array == []) {

                    // redéfinie la liste des followers le l'utitisateur en demande d'amis
                    array_push($followers, $array);

                    $user_network_selected->setMyFollowers($followers);
                
                    $network->setUser($user);
        
                    $network->setCreatedAt(new DateTimeImmutable());
                
                    $network->setUpdatedAt(new DateTimeImmutable());
                    $user_network_selected->setUpdatedAt(new DateTimeImmutable());
        
                    $network->setMyFriends([$array2]);
        
                    $networkRepository->save($network, true);
                    $networkRepository->save($user_network_selected, true);
        
                    return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER);

                } else {

                    if (array_key_exists($user->getId(), $followers[0])) {

                        return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER); 
                    
                    } else {
                        // redéfinie la liste des followers le l'utitisateur en demande d'amis
                        array_push($followers, $array);

                        $user_network_selected->setMyFollowers($followers);
                    
                        $network->setUser($user);
            
                        $network->setCreatedAt(new DateTimeImmutable());
                    
                        $network->setUpdatedAt(new DateTimeImmutable());
                        $user_network_selected->setUpdatedAt(new DateTimeImmutable());
            
                        $network->setMyFriends([$array2]);
            
                        $networkRepository->save($network, true);
                        $networkRepository->save($user_network_selected, true);
            
                        return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER); 
                    } 
                }
            }

        } else {

            if (isEmpty($user_network_selected)) {

                $new_user_network_selected = new Network();

                $new_user_network_selected->setUser($user_selected);

                $new_user_network_selected->setMyFollowers($array);

                $new_user_network_selected->setCreatedAt(new DateTimeImmutable());
            
                $user_network->setUpdatedAt(new DateTimeImmutable());
                $new_user_network_selected->setUpdatedAt(new DateTimeImmutable());

                // enregistre la liste d'amis de utilisateur
                $friends = $user_network->getMyFriends();

                // redefinie la liste des amis de l'utilisateur
                array_push($friends, $array2 );
                $user_network->setMyFriends($array2);

                $networkRepository->save($user_network, true);
                $networkRepository->save($new_user_network_selected, true);

                return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER); 

            } else {

                // enregistre la liste de followers de utilisateur en demande d'amis
                $followers = $user_network_selected->getMyFollowers();

                // redéfinie la liste des followers le l'utitisateur en demande d'amis
                array_push($followers, $array);

                if (array_key_exists($user->getId(), $followers[0])) {

                    return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER); 
                
                } else {
                    // redéfinie la liste des followers le l'utitisateur en demande d'amis
                    array_push($followers, $array);

                    $user_network_selected->setMyFollowers($followers);
    
                    $user_network->setUpdatedAt(new DateTimeImmutable());
                    $user_network_selected->setUpdatedAt(new DateTimeImmutable());
    
                    // enregistre la liste d'amis de utilisateur
                    $friends = $user_network->getMyFriends();
    
                    // redefinie la liste des amis de l'utilisateur
                    array_push($friends, $array2 );
                    $user_network->setMyFriends($array2);
    
                    $networkRepository->save($user_network, true);
                    $networkRepository->save($user_network_selected, true);
    
                    return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER);
                } 
            }
        }
    }

    #[Route('/again/{id}', name: 'app_network_again', methods: ['GET', 'POST'])]
    public function again($id, NetworkRepository $networkRepository): Response
    {
        $follow_id = (int) explode('-', $id, 2)[0];
    
        // init de l'utilisateur
        $user = $networkRepository->find($this->getUser());
        // init de l'utilisateur ajouter en amis
        $user_selected = $networkRepository->find($follow_id);

        // enregistre la liste de followers de utilisateur en demande d'amis
        $followers = $user_selected->getMyFollowers();
        
        // créer un tableaux avec comme clé id de l'utilisateur et en valeur 0 pour la demande d'amis
        $array = [$user->getId()=> 0];

        // redéfinie la liste des followers le l'utitisateur en demande d'amis
        array_push($followers, $array);
        $user_selected->setMyFollowers($followers);
        
        // enregistre en BDD les modifications porté aux utilisateurs
        $networkRepository->save($user_selected, true);

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
    public function edit($id, NetworkRepository $networkRepository): Response
    {   
        // recupere l'id du slug de utilisateur auquel on valide la demande d'ami
        $follow_id = (int) explode('-', $id, 2)[0];

        // init de l'utilisateur
        $user = $networkRepository->find($this->getUser());

        // recupere tous les followers de l'utilisateur
        $followers = $user->getMyFollowers()[0];
        
        if (array_key_exists($follow_id, $followers)) {

            
            if ($followers[$follow_id] == 1) {
              
                unset($followers[$follow_id]);
                $user->setMyFollowers($followers);
                $networkRepository->save($user, true);
        
                return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER);
            } else {

                $followers[$follow_id] = 1;
                $user->setMyFollowers([$followers]);
                $networkRepository->save($user, true);
        
                return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER);
            }

        } else {
  
            $followers[$follow_id] = 0;
            $user->setMyFollowers([$followers]);
            $networkRepository->save($user, true);
    
            return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER);
        } 
    }

    #[Route('/{id}', name: 'app_network_delete', methods: ['POST'])]
    public function delete($id, NetworkRepository $networkRepository): Response
    {   
        $follow_id = (int) explode('-', $id, 2)[0];

        // init de l'utilisateur
        $user = $networkRepository->find($this->getUser());

        $friends = $user->getMyfriends();
        unset($friends[array_search($follow_id, $friends)]);
      
        $user->setMyfriends($friends);
        $networkRepository->save($user, true);

        return $this->redirectToRoute('app_network_index', [], Response::HTTP_SEE_OTHER);
    }
}
