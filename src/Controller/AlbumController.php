<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use DateTimeImmutable;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use App\Repository\CategoryRepository;
use App\Repository\MediaRepository;
use Container9NZxCVb\getUserTypeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function new(Request $request, CategoryRepository $categoryRepository, AlbumRepository $albumRepository): Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          
            // recupere le fichier du formulaire
            $file = $form['imageCouv']->getData();
            // recupere le chemin absolut de la racine du projet pour cible sur le dossier de reception du fichier
            $destination = $this->getParameter('kernel.project_dir'). '/public/images/images';
            // recupere le mon de l'image par defaut
            $originalFileName = $file->getClientOriginalName();
            // met le fichier dans le dossier images
            $file->move($destination, $originalFileName);

            $album->setImageCouv('/images/images/'. $originalFileName);
            $album->setUser($this->getUser());
            $album->setCreatedAt(new DateTimeImmutable());
            $album->setUpdatedAt(new DateTimeImmutable());
            $albumRepository->save($album, true);

            return $this->redirectToRoute('app_media_index', [], Response::HTTP_SEE_OTHER);
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

    #[Route('/edit/{id}', name: 'app_album_edit', methods: ['GET', 'POST'])]
    public function edit($id, MediaRepository $mediaRepository): Response
    {
        $album_id = (int) explode('-', $id, 2)[0];
        $filter = [];
        $order = ['id'=> 'ASC'];

        $filter['album'] = $album_id;

        return $this->renderForm('album/edit.html.twig', [
            'media' => $mediaRepository->findBy($filter, $order),
        ]);
    }

    #[Route('/{id}', name: 'app_album_delete', methods: ['POST'])]
    public function delete(Request $request, Album $album, AlbumRepository $albumRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$album->getId(), $request->request->get('_token'))) {
            $albumRepository->remove($album, true);
        }

        return $this->redirectToRoute('app_media_index', [], Response::HTTP_SEE_OTHER);
    }
}
