<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotType;
use App\Form\PasswordsType;
use PhpParser\Builder\Method;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot/password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request,UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer): Response
    {

        $userAsk = new User();
        $form = $this->createForm(ForgotType::class, $userAsk);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $userRepository->findOneBy(['email' => $userAsk->getEmail()]);
            /* @var $user User */

            if ($user === null) {
                $this->addFlash('error', "L'e-mail renseigné n'existe pas ...");
                return $this->renderForm('security/login.html.twig');
            }
            
            $token = $tokenGenerator->generateToken();
      
            try{
                $user->setToken($token);
                $userRepository->save($user, true);
            } catch (\Exception $e) {
                return $this->renderForm('security/login.html.twig');            
            }
    
            $url = $this->generateUrl('app_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);
    
            $email = (new Email())
                ->from('contact@my-mediatech.fr')
                ->to($user->getEmail())
                ->subject('Mot de passe oublié')
                ->html("
                <h1>Bonjour</h1>
                <p>Cliqué sur ce liens pour redéfinir votre mot de pase: <a href='".$url."'>aller sur le site</a></p>
                ");
    
            $mailer->send($email);

            $this->addFlash('activate', 'Vous avez reçu un email pour modifier votre password.');

            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('forgot_password/index.html.twig', [
            'form' => $form
        ]);
    }

    #[Route(path: '/forgot/reset/{token}', name: 'app_reset_password')]
    public function activate( $token, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $userRepository->findOneBy(['token' => $token]);
        $form = $this->createForm(PasswordsType::class, $user);
        $form->handleRequest($request);

        if (!$user) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        

        if ($form->isSubmitted() && $form->isValid()) {

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $request->get('passwords')['password']['first']);
            
            $user->setPassword($hashedPassword);
            $user->setToken(null);
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('forgot_password/reset.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }
}
