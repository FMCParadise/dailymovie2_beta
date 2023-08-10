<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UsersFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Utilisez la classe Factory de Faker

        for ($i = 0; $i < 20; $i++) {
            $user = new User();

            // Utilisez les données générées par Faker pour les propriétés
            $user->setEmail($faker->unique()->email);
            $user->setRoles(['ROLE_AUTHOR']);

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);

            $user->setName($faker->firstName);
            $user->setFirstName($faker->lastName);
            $fileName =  $faker->image('public/assets/images/avatar', 200, 200, null, false);
            $user->setAvatar($fileName);
            $user->setRgpd($faker->randomElement([0, 1]));

            // Crée un objet DateTimeImmutable à partir de l'objet DateTime
            $createdAt = $faker->dateTimeThisYear();
            $user->setCreatedAt(\DateTimeImmutable::createFromMutable($createdAt));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
