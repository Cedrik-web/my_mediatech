<?php

namespace App\Controller;

use App\Entity\Album;
use App\Form\Album1Type;
use App\Repository\AlbumRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/album')]
class AlbumController extends AbstractController
{
    #[Route('/', name: 'app_album_index', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository): Response
    {
        return $this->render('album/index.html.twig', [
            'albums' => $albumRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_album_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AlbumRepository $albumRepository): Response
    {
        $album = new Album();
        $form = $this->createForm(Album1Type::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // recupere le fichier du formulaire
            $file = $form['imageCouv']->getData();
            // recupere le chemin absolut de la racine du projet pour cible sur le dossier de reception du fichier
            $destination = $this->getParameter('kernel.project_dir'). '/public/images';
            // recupere le mon de l'image par defaut
            $originalFileName = $file->getClientOriginalName();
            // met le fichier dans le dossier images
            $file->move($destination, $originalFileName);

            $album->setImageCouv('/images/'. $originalFileName);
            $album->setUser($this->getUser());
            $album->setCreatedAt(new DateTimeImmutable());
            $album->setUpdatedAt(new DateTimeImmutable());
            $albumRepository->save($album, true);

            return $this->redirectToRoute('app_album_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('album/new.html.twig', [
            'album' => $album,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_album_show', methods: ['GET'])]
    public function show(Album $album): Response
    {
        return $this->render('album/show.html.twig', [
            'album' => $album,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_album_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Album $album, AlbumRepository $albumRepository): Response
    {
        $form = $this->createForm(Album1Type::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $albumRepository->save($album, true);

            return $this->redirectToRoute('app_album_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('album/edit.html.twig', [
            'album' => $album,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_album_delete', methods: ['POST'])]
    public function delete(Request $request, Album $album, AlbumRepository $albumRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$album->getId(), $request->request->get('_token'))) {
            $albumRepository->remove($album, true);
        }

        return $this->redirectToRoute('app_album_index', [], Response::HTTP_SEE_OTHER);
    }
}
