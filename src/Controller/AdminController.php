<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(ProductRepository $productRepository, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté et est un administrateur
        if (!$user || !$user->isAdmin()) {
            return $this->redirectToRoute('app_home');
        }

        // Récupérer tous les produits
        $products = $productRepository->findAll();

        // Créer un nouveau produit via un formulaire
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les stocks pour chaque taille
            $stock = [
                intval($form->get('stock_0')->getData()),
                intval($form->get('stock_1')->getData()),
                intval($form->get('stock_2')->getData()),
                intval($form->get('stock_3')->getData()),
                intval($form->get('stock_4')->getData()),
            ];

            // Assigner le tableau JSON au champ stock de l'entité
            $product->setStock($stock);

            // Calcul de la quantité totale (somme des stocks)
            $totalQuantity = array_sum($stock);
            $product->setQuantity($totalQuantity);

            // Gestion de l'upload de fichier d'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                    return $this->redirectToRoute('app_admin');
                }

                $product->setImage('img/' . $newFilename);
            } else {
                // Si aucune image n'est téléchargée, utiliser l'image par défaut
                $product->setImage('img/default.jpeg');
            }

            // Persister le produit en base de données
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès.');

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/admin.html.twig', [
            'user' => $user,
            'products' => $products,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/product/{id}/edit', name: 'app_admin_product_edit')]
    public function edit(Product $product, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if (!$user || !$user->isAdmin()) {
            return $this->redirectToRoute('app_home');
        }

        // Créer le formulaire
        $form = $this->createForm(ProductType::class, $product);

        // Récupérer les valeurs de stock actuelles
        $stock = $product->getStock();

        // Pré-remplir les champs du formulaire avec les valeurs actuelles
        $form->get('stock_0')->setData($stock[0] ?? 0);
        $form->get('stock_1')->setData($stock[1] ?? 0);
        $form->get('stock_2')->setData($stock[2] ?? 0);
        $form->get('stock_3')->setData($stock[3] ?? 0);
        $form->get('stock_4')->setData($stock[4] ?? 0);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les stocks pour chaque taille et s'assurer que ce sont des entiers
            $stock = [
                intval($form->get('stock_0')->getData()),
                intval($form->get('stock_1')->getData()),
                intval($form->get('stock_2')->getData()),
                intval($form->get('stock_3')->getData()),
                intval($form->get('stock_4')->getData()),
            ];

            // Assigner le tableau JSON au champ stock de l'entité
            $product->setStock($stock);

            // Calcul de la quantité totale (somme des stocks)
            $totalQuantity = array_sum($stock);
            $product->setQuantity($totalQuantity);

            // Gestion de l'upload de fichier d'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                    return $this->redirectToRoute('app_admin');
                }

                $product->setImage('img/' . $newFilename);
            }

            // Sauvegarder les modifications en base de données
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès.');

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/admin/product/{id}/delete', name: 'app_admin_product_delete')]
    public function delete(Product $product, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user || !$user->isAdmin()) {
            return $this->redirectToRoute('app_home');
        }

        if ($product->getImage()) {
            $imagePath = $this->getParameter('images_directory') . '/' . $product->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'Produit supprimé avec succès.');

        return $this->redirectToRoute('app_admin');
    }
}