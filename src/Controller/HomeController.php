<?php

namespace App\Controller;

use App\Entity\Album;
use App\Repository\UserRepository;
use App\Repository\AlbumRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\Length;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(AlbumRepository $albumRepository): Response
    {
        
        return $this->render('home/index.html.twig', [
            'albums' => $albumRepository->findAll()
        ]);
    }

    #[Route('home/{id}', name: 'app_home_show', methods: ['GET'])]
    public function show(Album $album): Response
    {
        return $this->render('home/show.html.twig', [
            'album' => $album,
        ]);
    }
}
