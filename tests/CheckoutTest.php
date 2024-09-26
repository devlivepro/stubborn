<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class CheckoutTest extends WebTestCase
{
    public function testSuccessfulCheckout(): void
    {
        $client = static::createClient();

        // Récupérer l'EntityManager pour obtenir l'utilisateur de test
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneByEmail('johndoe@example.com');

        // Vérifie que l'utilisateur existe
        $this->assertNotNull($user, "L'utilisateur de test est introuvable.");

        // Connecte l'utilisateur de test
        $client->loginUser($user);

        // Ajouter un produit au panier avec une taille (ID du produit = 1)
        $client->request('POST', '/cart/add/1', [
            'size' => 'M',
        ]);

        // Vérifie que l'ajout au panier redirige bien
        $this->assertResponseStatusCodeSame(302);
        $client->followRedirect();

        // Vérifier que le produit a bien été ajouté au panier
        $session = $client->getRequest()->getSession();
        $cart = $session->get('cart');
        $this->assertNotNull($cart, 'Le panier est vide après l\'ajout du produit.');
        $this->assertArrayHasKey(1, $cart, 'Le produit avec l\'ID 1 n\'a pas été ajouté au panier.');

        // Vérification de la quantité et des détails du produit dans le panier
        $this->assertEquals(1, $cart[1]['quantity'], 'La quantité du produit ajouté n\'est pas correcte.');
        $this->assertEquals('M', $cart[1]['size'], 'La taille du produit ajouté n\'est pas correcte.');

        // Simuler la validation du panier en allant vers `/checkout`
        $client->request('GET', '/checkout');
        
        // Modifier ici : attendre un code 303 au lieu de 302
        $this->assertResponseStatusCodeSame(303); // Stripe redirige avec un code 303 See Other
        $this->assertTrue($client->getResponse()->isRedirect());

        // Vérifie que la redirection va bien vers Stripe
        $redirectUrl = $client->getResponse()->headers->get('Location');
        $this->assertStringContainsString('stripe.com', $redirectUrl);
    }
}