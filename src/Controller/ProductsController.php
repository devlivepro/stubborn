<?php

// src/Controller/ProductsController.php
namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductsController extends AbstractController
{
    #[Route('/products', name: 'app_products')] // Route associée à la méthode index
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        // Récupération du paramètre de fourchette de prix envoyé par le formulaire
        $priceRange = $request->query->get('priceRange');

        // Valeurs par défaut pour les prix min et max
        $minPrice = 0;
        $maxPrice = 1000;

        // Si une fourchette de prix est sélectionnée, on ajuste minPrice et maxPrice
        if ($priceRange) {
            list($minPrice, $maxPrice) = explode('-', $priceRange);
        }

        // Récupération des produits filtrés par la fourchette de prix
        $products = $productRepository->findByPriceRange($minPrice, $maxPrice);

        return $this->render('products/products.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show')] // Route pour un produit individuel
    public function show(Product $product): Response
    {
        return $this->render('products/product.html.twig', [
            'product' => $product,
        ]);
    }
}