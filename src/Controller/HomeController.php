<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    private $session;

    public function __construct(RequestStack $requestStack) {
        $this->session = $requestStack->getSession();
    }
    
    #[Route('/', name: 'app_home')]
    public function index(Request $request, AlbumRepository $albumRepository, CategoryRepository $categoryRepository): Response
    {
        $filter = [];
        $category = $request->get('category');
        $order = $request->get('find');
        

        if (!empty($category)) {
            $filter['category'] = $category;
        }

        if (!empty($order)) {

            return $this->render('home/index.html.twig', [
                'albums' => $albumRepository->search($order),
                'categories' => $categoryRepository->findAll()
            ]);
        } else {

            $order = ['name'=> 'ASC'];

            return $this->render('home/index.html.twig', [
                'albums' => $albumRepository->findBy($filter, $order),
                'categories' => $categoryRepository->findAll()
            ]);
        }
    }

    #[Route('home/{id}', name: 'app_home_show', methods: ['GET'])]
    public function show($id, MediaRepository $mediaRepository): Response
    {   
        $album_id = (int) explode('-', $id, 2)[0];
        $filter = [];
        $order = ['id'=> 'ASC'];

        $filter['album'] = $album_id;

        return $this->render('home/show.html.twig', [
            'media' => $mediaRepository->findBy($filter, $order),
        ]);
    }

}
