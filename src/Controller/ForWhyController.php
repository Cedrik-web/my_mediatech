<?php

namespace App\Controller;

use App\Entity\ForWhy;
use App\Form\ForWhyType;
use App\Repository\ForWhyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/for/why')]
class ForWhyController extends AbstractController
{
    #[Route('/', name: 'app_for_why_index', methods: ['GET'])]
    public function index(ForWhyRepository $forWhyRepository): Response
    {
        return $this->render('for_why/index.html.twig', [
            'for_whies' => $forWhyRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_for_why_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ForWhyRepository $forWhyRepository): Response
    {
        $forWhy = new ForWhy();
        $form = $this->createForm(ForWhyType::class, $forWhy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $forWhyRepository->save($forWhy, true);

            return $this->redirectToRoute('app_for_why_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('for_why/new.html.twig', [
            'for_why' => $forWhy,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_for_why_show', methods: ['GET'])]
    public function show(ForWhy $forWhy): Response
    {
        return $this->render('for_why/show.html.twig', [
            'for_why' => $forWhy,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_for_why_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ForWhy $forWhy, ForWhyRepository $forWhyRepository): Response
    {
        $form = $this->createForm(ForWhyType::class, $forWhy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $forWhyRepository->save($forWhy, true);

            return $this->redirectToRoute('app_for_why_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('for_why/edit.html.twig', [
            'for_why' => $forWhy,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_for_why_delete', methods: ['POST'])]
    public function delete(Request $request, ForWhy $forWhy, ForWhyRepository $forWhyRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$forWhy->getId(), $request->request->get('_token'))) {
            $forWhyRepository->remove($forWhy, true);
        }

        return $this->redirectToRoute('app_for_why_index', [], Response::HTTP_SEE_OTHER);
    }
}
