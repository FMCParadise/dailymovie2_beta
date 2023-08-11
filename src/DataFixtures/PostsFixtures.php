<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use App\Entity\Posts;
use App\Entity\User;
use App\Services\Slugger;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PostsFixtures extends Fixture implements DependentFixtureInterface
{

    private string $dir_full;

    private string $dir_square;

    public function __construct(ParameterBagInterface $parameter)
    {
        $this->dir_full = $parameter->get('full_images_directory');
        $this->dir_square = $parameter->get('square_images_directory');
    }


    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
            CategoriesFixtures::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        $users = $manager->getRepository(User::class)->findAll();
        $categories = $manager->getRepository(Categories::class)->findAll();

        foreach ($users as $user) {
            for ($i = 0; $i < 2; $i++) {
                $post = new Posts();
                $post->setUserId($user);
                $post->setContent($faker->paragraph);
                $post->setSlug(Slugger::slugify("titre du film") . '-' . $faker->randomNumber(4));
                $post->setTitle('titre du film');
                $randomCategories = $faker->randomElements($categories, mt_rand(1, 9));

                // Générer un nom unique pour l'image
                $imageFileName = uniqid() . '.jpg';

                // Stocker l'image dans le répertoire "full"
                $fullImagePath = $this->dir_full.'/'.$imageFileName;
                copy($faker->image(null ,1200 , 600), $fullImagePath);

                // Générer une version carrée de l'image
                $squareImagePath = $this->dir_square .'/'. $imageFileName;
                copy($faker->image(null ,600,600), $squareImagePath);

                // Stocker le nom de l'image dans l'entité Posts
                $post->setImage($imageFileName);

                foreach ($randomCategories as $category) {
                    $post->addCategoriesId($category);
                }

                $manager->persist($post);
            }
        }

        $manager->flush();
    }
}