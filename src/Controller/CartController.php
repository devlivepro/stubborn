<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        // Ici, vous pouvez ajouter la logique pour afficher le panier
        // Par exemple, récupérer les articles du panier depuis la session

        return $this->render('cart.html.twig', [
            'controller_name' => 'CartController',
        ]);
    }
}
