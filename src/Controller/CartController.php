<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
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

        // Récupérer la taille sélectionnée dans le formulaire
        $size = $request->request->get('size');
        if (!$size) {
            $this->addFlash('error', 'Veuillez sélectionner une taille.');
            return $this->redirectToRoute('app_cart');
        }

        // Vérifier la disponibilité du stock pour la taille sélectionnée
        $stock = $product->getStock(); // Stock est un tableau [XS, S, M, L, XL]
        $sizeIndex = $this->getSizeIndex($size);

        if ($sizeIndex === null || $stock[$sizeIndex] < 1) {
            $this->addFlash('error', 'Le produit est en rupture de stock pour cette taille.');
            return $this->redirectToRoute('app_cart');
        }

        // Ajouter ou mettre à jour le produit dans le panier
        if (!isset($cart[$id])) {
            $cart[$id] = [
                'product' => $product->getId(), // Stocker l'ID du produit
                'quantity' => 0,
                'size' => $size,
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

            if ($product) {
                $cartWithData[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'size' => $item['size'],
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

        foreach ($cart as $id => $item) {
            $product = $productRepository->find($id);
            if ($product) {
                // Vérifier à nouveau la quantité disponible avant la validation
                $stock = $product->getStock();
                $sizeIndex = $this->getSizeIndex($item['size']);

                if ($sizeIndex === null || $stock[$sizeIndex] < $item['quantity']) {
                    $this->addFlash('error', 'Stock insuffisant pour ' . $product->getName());
                    return $this->redirectToRoute('app_cart');
                }

                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $product->getName() . ' (' . $item['size'] . ')',
                        ],
                        'unit_amount' => $product->getPrice() * 100,
                    ],
                    'quantity' => $item['quantity'],
                ];
            }
        }

        Stripe::setApiKey($this->getParameter('STRIPE_SECRET_KEY'));

        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_stripe_success', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_cart', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($checkoutSession->url, 303);
    }

    #[Route('/success', name: 'app_stripe_success')]
    public function success(Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        $cartSession = $request->getSession()->get('cart', []);

        if (empty($cartSession)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        // Créer un nouvel objet Cart pour cet utilisateur
        $user = $this->getUser();
        $cart = new Cart();
        $cart->setUser($user);
        $cart->setCreatedAt(new \DateTime());

        // Parcourir les éléments du panier et mettre à jour le stock
        foreach ($cartSession as $id => $item) {
            $product = $productRepository->find($id);

            if ($product) {
                // Récupérer et décrémenter le stock pour la taille sélectionnée
                $stock = $product->getStock();
                $sizeIndex = $this->getSizeIndex($item['size']);

                if ($sizeIndex !== null && $stock[$sizeIndex] >= $item['quantity']) {
                    // Décrémenter le stock
                    $stock[$sizeIndex] -= $item['quantity'];
                    $product->setStock($stock);

                    // Mettre à jour la quantité totale du produit
                    $totalQuantity = array_sum($stock);
                    $product->setQuantity($totalQuantity);

                    // Ajouter le produit à l'objet Cart
                    $cart->addProduct($product);

                    // Persist les changements du produit
                    $em->persist($product);
                }
            }
        }

        // Persist le Cart dans la base de données
        $em->persist($cart);
        $em->flush();

        // Vider le panier après un paiement réussi
        $request->getSession()->remove('cart');

        return $this->render('stripe/success.html.twig');
    }

    /**
     * Méthode utilitaire pour obtenir l'index de la taille dans le tableau de stock
     */
    private function getSizeIndex(string $size): ?int
    {
        $sizeMap = [
            'XS' => 0,
            'S' => 1,
            'M' => 2,
            'L' => 3,
            'XL' => 4
        ];

        return $sizeMap[strtoupper($size)] ?? null;
    }
}