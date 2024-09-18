<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ModifyDeliveryAddressType;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        // Récupérer les informations de l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier que l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login'); // Redirige vers la page de connexion s'il n'est pas connecté
        }

        return $this->render('account.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/account/modify-address', name: 'app_modify_delivery_address')]
    public function modifyDeliveryAddress(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Créer le formulaire pour modifier l'adresse de livraison
        $form = $this->createForm(ModifyDeliveryAddressType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre adresse de livraison a été mise à jour.');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/modify-address.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}