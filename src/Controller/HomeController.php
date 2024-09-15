<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        // Récupérer les 3 produits mis en avant (highlighted)
        $highlightedProducts = $productRepository->findBy(
            ['highlighted' => true], // Condition : seulement les produits "mis en avant"
            null,                    // Aucun tri particulier
            3                        // Limite : 3 produits
        );

        return $this->render('home.html.twig', [
            'highlightedProducts' => $highlightedProducts,
        ]);
    }
}


