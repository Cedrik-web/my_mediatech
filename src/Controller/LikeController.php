<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Media;
use App\Form\LikeType;
use App\Repository\LikeRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use function PHPSTORM_META\type;

#[Route('/like')]
class LikeController extends AbstractController
{
    private $session;

    public function __construct(RequestStack $requestStack) {
        $this->session = $requestStack->getSession();
    }
    
    #[Route('/', name: 'app_like_index', methods: ['GET'])]
    public function index(LikeRepository $likeRepository): Response
    {
        return $this->render('like/index.html.twig', [
            'likes' => $likeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_like_new', methods: ['GET', 'POST'])]
    public function new(Request $request,MediaRepository $mediaRepository, LikeRepository $likeRepository): Response
    {
        $like = new Like();
        $media = new Media();
        $form = $this->createForm(LikeType::class, $like);
        $form->handleRequest($request);
       
        $filter = ['media'=> $_GET['id']];
        $order = ['id'=>'ASC'];
        $data = $likeRepository->findBy($filter, $order);
        $media = $mediaRepository->findBy($_GET, $order);
        $id = strval($media[0]->getId());
      
        if (empty($data)) {
        
            $like->setCreatedAt(new DateTimeImmutable());
            $like->setLiked(1);
            $like->setView(0);
            $like->setMedia($this->$id);

            $likeRepository->save($like, true);

            return $this->redirectToRoute('app_like_index', [], Response::HTTP_SEE_OTHER);
            
        } else {
           
            $like->setCreatedAt(new DateTimeImmutable());
            $add = $data['liked'];
            $like->setLiked($add+1);
            $like->setView(0);

            $likeRepository->save($like, true);

            return $this->redirectToRoute('app_like_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('like/new.html.twig', [
            'like' => $like,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_like_show', methods: ['GET'])]
    public function show(Like $like): Response
    {
        return $this->render('like/show.html.twig', [
            'like' => $like,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_like_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Like $like, LikeRepository $likeRepository): Response
    {
        $form = $this->createForm(LikeType::class, $like);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $likeRepository->save($like, true);

            return $this->redirectToRoute('app_like_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('like/edit.html.twig', [
            'like' => $like,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_like_delete', methods: ['POST'])]
    public function delete(Request $request, Like $like, LikeRepository $likeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$like->getId(), $request->request->get('_token'))) {
            $likeRepository->remove($like, true);
        }

        return $this->redirectToRoute('app_like_index', [], Response::HTTP_SEE_OTHER);
    }
}
