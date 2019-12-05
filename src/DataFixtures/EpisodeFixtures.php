<?php

namespace App\DataFixtures;

use App\Service\Slugify;
use Faker;
use App\Entity\Episode;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        for($i = 0; $i < 500; $i++) {
            $episode = new Episode();
            $title = $faker->title;
            $episode->setTitle($title);
            $number = $faker->randomDigitNotNull;
            $episode->setNumber($number);
            $synopsis = $faker->paragraph;
            $episode->setSynopsis($synopsis);

            $episode->setSeason($this->getReference("season_" . rand(1,49)));
            $slugify = new Slugify();
            $slug = $slugify->generate($episode->getTitle());
            $episode->setSlug($slug);

            $manager->persist($episode);
        }
        $manager->flush();

    }

    public function getDependencies()
    {
        return [SeasonFixtures::class];
    }
}