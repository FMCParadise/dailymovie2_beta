<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use App\Services\Slugger;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoriesFixtures extends Fixture
{

    public function __construct( private Slugger $slugger)
    {
    }

    public function load(ObjectManager $manager )
    {
        $faker = Factory::create();

        $categoryNames = [
            'action',
            'aventure',
            'animation',
            'comédie',
            'crime',
            'fantastique',
            'romance',
            'guerre',
            'science-fiction'];

        foreach ($categoryNames as $name) {
            $category = new Categories();
            $category->setName($name);
            $slug = $this->slugger::slugify($name) ;
            $category->setSlug($slug); // Create slug from name
            $manager->persist($category);
        }

        $manager->flush();
    }
}