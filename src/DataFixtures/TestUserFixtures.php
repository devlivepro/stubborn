<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestUserFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création utilisateur 1 dans db-test
        $user = new User();
        $user->setEmail('johndoe@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'johndoe1234'));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setUsername('johndoetest');
        $user->setDeliveryAddress('8 rue paul roberto 75001 Paris');
        $user->setIsVerified(true);
        $user->setIsAdmin(true);
        $manager->persist($user);
        $manager->flush();
    }

}
