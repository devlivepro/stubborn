<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
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
        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

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

        try {
            // Créer la session de paiement Stripe avec tous les articles du panier
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->generateUrl('app_stripe_success', [], Response::HTTP_SEE_OTHER),
                'cancel_url' => $this->generateUrl('app_cart', [], Response::HTTP_SEE_OTHER),
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la création de la session de paiement.');
            return $this->redirectToRoute('app_cart');
        }

        return $this->redirect($session->url, 303);
    }

    #[Route('/stripe/success', name: 'app_stripe_success')]
    public function success(Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer le panier depuis la session
        $cartSession = $request->getSession()->get('cart', []);

        if (empty($cartSession)) {
            $this->addFlash('error', 'Aucun produit dans le panier.');
            return $this->redirectToRoute('app_products');
        }

        // Créer un nouvel objet Cart pour cet utilisateur
        $cart = new Cart();
        $cart->setUser($user);
        $cart->setCreatedAt(new \DateTime());

        // Ajouter les produits dans la table `cart_products` et mettre à jour le stock
        foreach ($cartSession as $item) {
            /** @var Product $product */
            $product = $em->getRepository(Product::class)->find($item['product']->getId());

            // Vérification de l'existence du produit dans la base de données
            if (!$product) {
                $this->addFlash('error', 'Produit introuvable.');
                return $this->redirectToRoute('app_cart');
            }

            // Récupérer le stock actuel
            $stock = $product->getStock(); // Array [XS, S, M, L, XL]
            $size = $item['size']; // Taille sélectionnée (XS, S, M, L, XL)
            $sizeIndex = $this->getSizeIndex($size);

            // Vérifier la taille et la quantité disponible
            if ($sizeIndex !== null && $stock[$sizeIndex] >= $item['quantity']) {
                // Décrémenter le stock pour la taille choisie
                $stock[$sizeIndex] -= $item['quantity'];

                // Mettre à jour le stock dans le produit
                $product->setStock($stock);

                // Calculer et mettre à jour la quantité totale
                $totalQuantity = array_sum($stock); // Recalculer la quantité totale
                $product->setQuantity($totalQuantity);

                // Sauvegarder le produit mis à jour
                $em->persist($product);

                // Ajouter le produit au panier
                $cart->addProduct($product);
            } else {
                $this->addFlash('error', 'Quantité non disponible pour la taille sélectionnée.');
                return $this->redirectToRoute('app_cart');
            }
        }

        // Sauvegarder le panier dans la base de données
        $em->persist($cart);
        $em->flush();

        // Vider le panier après le paiement
        $request->getSession()->remove('cart');

        // Rediriger vers la page de succès
        return $this->render('stripe/success.html.twig');
    }

    /**
     * Obtenir l'index correspondant à la taille sélectionnée
     */
    private function getSizeIndex(string $size): ?int
    {
        // Assigner les index des tailles dans le tableau stock
        switch (strtoupper($size)) {
            case 'XS':
                return 0;
            case 'S':
                return 1;
            case 'M':
                return 2;
            case 'L':
                return 3;
            case 'XL':
                return 4;
            default:
                return null;
        }
    }
}