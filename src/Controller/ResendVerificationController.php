<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class ResendVerificationController extends AbstractController
{
    private $entityManager;
    private $verifyEmailHelper;
    private $mailer;

    public function __construct(EntityManagerInterface $entityManager, VerifyEmailHelperInterface $verifyEmailHelper, MailerInterface $mailer)
    {
        $this->entityManager = $entityManager;
        $this->verifyEmailHelper = $verifyEmailHelper;
        $this->mailer = $mailer;
    }

    #[Route('/resend-verification', name: 'app_resend_verification')]
    public function resendVerification(Request $request): Response
    {
        $email = $request->request->get('email'); // Récupérer l'email du formulaire

        if ($email) {
            // Rechercher l'utilisateur dans la base de données
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            // Si l'utilisateur n'existe pas ou est déjà vérifié, afficher un message d'erreur
            if (!$user) {
                $this->addFlash('error', 'Aucun compte trouvé avec cet email.');
            } elseif ($user->isVerified()) {
                $this->addFlash('info', 'Votre compte est déjà vérifié.');
            } else {
                // Générer un nouvel email de vérification
                $signatureComponents = $this->verifyEmailHelper->generateSignature(
                    'app_verify_email', // Nom de la route de vérification
                    $user->getId(),
                    $user->getEmail(),
                    ['id' => $user->getId()]
                );

                // Envoyer l'email
                $emailMessage = (new TemplatedEmail())
                    ->from(new Address('stubbornphp@gmail.com', 'Stubborn'))
                    ->to($user->getEmail())
                    ->subject('Veuillez confirmer votre adresse e-mail')
                    ->htmlTemplate('emails/confirm_email.html.twig')
                    ->context([
                        'signedUrl' => $signatureComponents->getSignedUrl(),
                        'expiresAt' => $signatureComponents->getExpiresAt()->format('Y-m-d H:i:s'),
                    ]);

                $this->mailer->send($emailMessage);

                // Ajouter un message flash pour informer l'utilisateur
                $this->addFlash('success', 'Un nouvel email de confirmation a été envoyé.');
            }
        }

        return $this->render('security/resend_verification.html.twig');
    }
}