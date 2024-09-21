<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un utilisateur admin de test
        $user = new User();
        $user->setEmail('johndoe@example.com');
        $user->setUsername('johndoe'); // Vérifiez que ce champ existe dans votre entité User
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setDeliveryAddress('8 rue Paul Roberto, 75001 Paris');
        $user->setIsAdmin(true);
        $manager->persist($user);

        $manager->flush();
    }
    
}