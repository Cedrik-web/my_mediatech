<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use DateTimeImmutable;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('/register', name: 'app_user_register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // définie le role
            $user->setRoles(['ROLE_USER']);
            // définie le token de validation
            $user->setToken(bin2hex(random_bytes(20)));
            // définie le statue sur inactif
            $user->setActive(0);
            // définie la date de création aisni que celle de update
            $user->setCreatedAt(new DateTimeImmutable);
            $user->setUpdatedAt(new DateTimeImmutable);

            // fonction qui hash le password
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $request->get('user')['password']['first']
            );
            // hash le password
            $user->setPassword($hashedPassword);

            // fonction d'envoie de mail
            $email = (new Email())
            ->from('contact@my-mediatech.fr')
            ->to($user->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Activez votre compte My MediATech')
            ->html('
                <h1>Bonjour</h1>
                <p>See Twig integration for better HTML integration!</p>
                <a href="'. $this->generateUrl('app_activate', ['token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL).'">Activer votre compte</a>
            ');

            $mailer->send($email);

            // sauvegarde de l'utilisateur
            $userRepository->save($user, true);

            $this->addFlash('activate', 'Vous avez reçu un email pour activer votre compte.');

            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/register.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route(path: '/activate/{token}', name: 'app_activate')]
    public function activate($token, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['token' => $token]);
        if ($user) {
            $user->setActive(true);
            $user->setToken(null);
            $userRepository->save($user, true);
        }
      
        return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(AuthenticationUtils $authenticationUtils): Response
    {
       
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->redirect($this->generateUrl('app_login'));
    }
}
