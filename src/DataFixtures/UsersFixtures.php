<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UsersFixtures extends Fixture
{
    private $passwordHasher;

    private string $dir_avatar;

    public function __construct(UserPasswordHasherInterface $passwordHasher , ParameterBagInterface $parameter)
    {
        $this->passwordHasher = $passwordHasher;

        $this->dir_avatar = $parameter->get('avatar_images_directory');

        //create dir avatar if not existe
        if (!is_dir($this->dir_avatar)) {
            mkdir($this->dir_avatar, 0700, true);
        }

    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Utilisez la classe Factory de Faker

        //create admin
        $adminUser =  new user() ;
        $adminUser = $adminUser->setEmail("admin@admin.fr");
        $adminUser->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, 'password123');
        $adminUser->setPassword($hashedPassword);
        $adminUser->setName($faker->firstName);
        $adminUser->setFirstName($faker->lastName);


        $fileName =  $faker->image($this->dir_avatar, 200, 200, null, false);
        $adminUser->setAvatar($fileName);
        $adminUser->setRgpd(1);

        $createdAt = $faker->dateTimeThisYear();
        $adminUser->setCreatedAt(\DateTimeImmutable::createFromMutable($createdAt));
        $manager->persist($adminUser);


        //create users
        for ($i = 0; $i < 2; $i++) {
            $user = new User();
            // Utilisez les données générées par Faker pour les propriétés
            $user->setEmail($faker->unique()->email);
            $user->setRoles(['ROLE_AUTHOR']);
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);
            $user->setName($faker->firstName);
            $user->setFirstName($faker->lastName);
            $fileName =  $faker->image($this->dir_avatar, 200, 200, null, false);
            $user->setAvatar($fileName);
            $user->setRgpd(1);

            // Crée un objet DateTimeImmutable à partir de l'objet DateTime
            $createdAt = $faker->dateTimeThisYear();
            $user->setCreatedAt(\DateTimeImmutable::createFromMutable($createdAt));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
