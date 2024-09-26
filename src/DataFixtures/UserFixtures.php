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
        // Création utilisateur 1
        $user1 = new User();
        $user1->setEmail('johndoe@hotmail.com');
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'johndoe1234'));
        $user1->setRoles(['ROLE_USER']);
        $user1->setUsername('johndoe');
        $user1->setDeliveryAddress('5 rue de la voie 75001 Paris');
        $user1->setIsVerified(true);
        $user1->setIsAdmin(false);
        $manager->persist($user1);
        $manager->flush();

        // Création utilisateur 2
        $user2 = new User();
        $user2->setEmail('johndoeadmin@hotmail.com');
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'johndoe1234'));
        $user2->setRoles(['ROLE_USER']);
        $user2->setUsername('johndoeadmin');
        $user2->setDeliveryAddress('6 rue de la voie 75001 Paris');
        $user2->setIsVerified(true);
        $user2->setIsAdmin(true);
        $manager->persist($user2);
        $manager->flush();

        // Création utilisateur 3
        $user3 = new User();
        $user3->setEmail('johndoe0@hotmail.com');
        $user3->setPassword($this->passwordHasher->hashPassword($user3, 'johndoe1234'));
        $user3->setRoles(['ROLE_USER']);
        $user3->setUsername('johndoe0');
        $user3->setDeliveryAddress('7 rue de la voie 75001 Paris');
        $user3->setIsVerified(false);
        $user3->setIsAdmin(false);
        $manager->persist($user3);
        $manager->flush();

        // Création utilisateur 4 pour stubborn_db_test
        $user4 = new User();
        $user4->setEmail('johndoe@example.com');
        $user4->setPassword($this->passwordHasher->hashPassword($user4, 'johndoe1234'));
        $user4->setRoles(['ROLE_ADMIN']);
        $user4->setUsername('johndoetest');
        $user4->setDeliveryAddress('8 rue paul roberto 75001 Paris');
        $user4->setIsVerified(true);
        $user4->setIsAdmin(true);
        $manager->persist($user4);
        $manager->flush();
    }

}
