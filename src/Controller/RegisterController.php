<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegisterController extends AbstractController
{
    private VerifyEmailHelperInterface $verifyEmailHelper;
    private MailerInterface $mailer;
    private EntityManagerInterface $entityManager;

    public function __construct(VerifyEmailHelperInterface $verifyEmailHelper, MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        $this->verifyEmailHelper = $verifyEmailHelper;
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Assurez-vous que l'utilisateur a au moins le rôle "ROLE_USER"
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(false); // Ajouter le statut non vérifié

            // Persist user but do not verify yet
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Generate email verification signature and send verification email
            $signatureComponents = $this->verifyEmailHelper->generateSignature(
                'app_verify_email', // route name for verification
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()] // parameters
            );

            // Send email
            $email = (new TemplatedEmail())
                ->from(new Address('stubbornphp@gmail.com', 'Stubborn'))
                ->to($user->getEmail())
                ->subject('Veuillez confirmer votre adresse e-mail')
                ->htmlTemplate('emails/confirm_email.html.twig')
                ->context([
                    'signedUrl' => $signatureComponents->getSignedUrl(),
                    'expiresAt' => $signatureComponents->getExpiresAt()->format('Y-m-d H:i:s'),
                ]);

            $this->mailer->send($email);

            return $this->redirectToRoute('app_verify_email_notice');
        }

        return $this->render('register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    // Méthode pour afficher la page après enregistrement et avant vérification de l'email
    #[Route('/verify/email/notice', name: 'app_verify_email_notice')]
    public function emailNotice(): Response
    {
        return $this->render('emails/verify_email_notice.html.twig');
    }

    // Méthode pour vérifier l'email de l'utilisateur
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $userId = $request->get('id');
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
            $user->setIsVerified(true);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre adresse e-mail a été vérifiée.');
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $exception->getReason());
        }

        return $this->redirectToRoute('app_home');
    }
}