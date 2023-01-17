<?php

namespace App\Controller;

use App\Entity\Media;
use DateTimeImmutable;
use App\Form\MediaType;
use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/media')]
class MediaController extends AbstractController
{
    private $session;

    public function __construct(RequestStack $requestStack) {
        $this->session = $requestStack->getSession();
    }
    
    #[Route('/', name: 'app_media_index', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository): Response
    {
        
        return $this->render('media/index.html.twig', [
            'albums' => $albumRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_media_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MediaRepository $mediaRepository): Response
    {
        $medium = new Media();
        $form = $this->createForm(MediaType::class, $medium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // recupere le fichier d'un image en entier
            $file = $form['link']->getData();
            // recupere le chemin absolut (racine du site ) de site et cible sur le dossier public
            $destination = $this->getParameter('kernel.project_dir').'/public/images';
            // recupere le nom de l'image original
            $originalFileName = $file->getClientOriginalName();

            // recupere la taille de l'image sous forme d'un tableau de trois elements width, height, et string
            $taille = getimagesize(($file));

            if ($taille[0] > $taille[1]) {
                $medium->setFormat('landscape');
            } else {
                $medium->setFormat('portrait');
            };

            // reattribut les nouveaux parametre eu fichier image
            $file->move($destination, $originalFileName);
            // definie le lien de l'adresse de l'image sur le media
            $medium->setLink('/images/'. $originalFileName);
            
            $medium->setCreatedAt(new DateTimeImmutable());

            $mediaRepository->save($medium, true);

            return $this->redirectToRoute('app_media_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('media/new.html.twig', [
            'medium' => $medium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_media_show', methods: ['GET'])]
    public function show($id, MediaRepository $mediaRepository): Response
    {   
        $album_id = (int) explode('-', $id, 2)[0];
        $filter = [];
        $order = ['id'=> 'ASC'];

        $filter['album'] = $album_id;

        return $this->render('media/show.html.twig', [
            'media' => $mediaRepository->findBy($filter, $order),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_media_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Media $medium, MediaRepository $mediaRepository): Response
    {
        $form = $this->createForm(MediaType::class, $medium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mediaRepository->save($medium, true);

            return $this->redirectToRoute('app_media_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('media/edit.html.twig', [
            'medium' => $medium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_media_delete', methods: ['POST'])]
    public function delete(Request $request, Media $medium, MediaRepository $mediaRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$medium->getId(), $request->request->get('_token'))) {
            $mediaRepository->remove($medium, true);
        }

        return $this->redirectToRoute('app_media_index', [], Response::HTTP_SEE_OTHER);
    }
}
