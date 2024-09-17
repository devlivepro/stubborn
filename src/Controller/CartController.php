<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;

class CartController extends AbstractController
{
    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function addToCart($id, Request $request, ProductRepository $productRepository): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        // Récupérer le produit depuis la base de données
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Le produit n\'existe pas.');
        }

        // Vérifier si le stock est suffisant
        if ($product->getQuantity() < 1) {
            $this->addFlash('error', 'Le produit est en rupture de stock.');
            return $this->redirectToRoute('app_cart');
        }

        // Récupérer la taille sélectionnée dans le formulaire
        $size = $request->request->get('size', 'M'); // Taille par défaut: M

        // Initialiser correctement chaque élément du panier (produit + quantité + taille)
        if (!isset($cart[$id])) {
            $cart[$id] = [
                'product' => $product->getId(), // Stocker l'identifiant du produit
                'quantity' => 0,                // Initialiser la quantité
                'size' => $size,                // Stocker la taille sélectionnée
            ];
        }

        // Incrémenter la quantité du produit dans le panier
        $cart[$id]['quantity']++;

        // Sauvegarder le panier dans la session
        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function removeFromCart($id, Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);

        // Si le produit est dans le panier, le supprimer
        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        // Mettre à jour le panier dans la session
        $request->getSession()->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart', name: 'app_cart')]
    public function showCart(Request $request, ProductRepository $productRepository): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $cartWithData = [];

        // Parcourir les éléments du panier
        foreach ($cart as $id => $item) {
            $product = $productRepository->find($id);

            // Assurer la structure correcte et gérer les erreurs possibles
            if ($product) {
                $cartWithData[] = [
                    'product' => $product,
                    'quantity' => isset($item['quantity']) ? $item['quantity'] : 1, // Accéder à la quantité ou définir à 1 par défaut
                    'size' => isset($item['size']) ? $item['size'] : 'M',           // Afficher la taille sélectionnée
                ];
            }
        }

        return $this->render('cart/cart.html.twig', [
            'cart' => $cartWithData,
        ]);
    }

    #[Route('/checkout', name: 'app_stripe_checkout')]
    public function checkout(Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $lineItems = [];

        // Préparer les articles pour la session de paiement Stripe
        foreach ($cart as $id => $item) {
            $product = $productRepository->find($id);
            if ($product) {
                // Vérifier à nouveau la quantité en stock avant la validation
                if ($product->getQuantity() < $item['quantity']) {
                    $this->addFlash('error', 'Stock insuffisant pour ' . $product->getName());
                    return $this->redirectToRoute('app_cart');
                }

                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $product->getName() . ' (' . $item['size'] . ')', // Inclure la taille dans le nom du produit
                        ],
                        'unit_amount' => $product->getPrice() * 100, // Prix en centimes
                    ],
                    'quantity' => $item['quantity'], // Utiliser la quantité ici
                ];
            }
        }

        // Initialiser Stripe avec la clé secrète
        Stripe::setApiKey($this->getParameter('STRIPE_SECRET_KEY'));

        // Créer une session de paiement
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems, // Utilisation directe de $lineItems
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_stripe_success', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_cart', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($checkoutSession->url, 303);
    }

    #[Route('/success', name: 'app_stripe_success')]
    public function success(Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        $cart = $request->getSession()->get('cart', []);

        // Mettre à jour les quantités en stock après le paiement
        foreach ($cart as $id => $item) {
            $product = $productRepository->find($id);
            if ($product) {
                // Décrémenter la quantité disponible dans le stock
                $newStock = $product->getQuantity() - $item['quantity'];
                $product->setQuantity($newStock);

                $em->persist($product);
            }
        }

        $em->flush(); // Sauvegarder les changements en base de données

        // Vider le panier après le paiement réussi
        $request->getSession()->remove('cart');

        return $this->render('stripe/success.html.twig');
    }
}