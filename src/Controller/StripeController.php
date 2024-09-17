<?php

// src/Controller/StripeController.php
namespace App\Controller;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    #[Route('/stripe/checkout', name: 'app_stripe_checkout')]
    public function checkout(Request $request): Response
    {
        // Initialiser Stripe avec la clé secrète
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        // Récupérer le panier depuis la session
        $cart = $request->getSession()->get('cart', []);
        $lineItems = [];

        // Transformer chaque élément du panier en ligne de commande pour Stripe
        foreach ($cart as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['product']->getName(), // Nom du produit
                    ],
                    'unit_amount' => $item['product']->getPrice() * 100, // Prix en centimes
                ],
                'quantity' => $item['quantity'], // Quantité d'articles
            ];
        }

        // Créer la session de paiement Stripe avec tous les articles du panier
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_stripe_success', [], Response::HTTP_SEE_OTHER),
            'cancel_url' => $this->generateUrl('app_cart', [], Response::HTTP_SEE_OTHER),
        ]);

        return $this->redirect($session->url, 303);
    }

    #[Route('/stripe/success', name: 'app_stripe_success')]
    public function success(Request $request): Response
    {
        // Vider le panier après le paiement
        $request->getSession()->remove('cart');

        return $this->render('stripe/success.html.twig');
    }
}