<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Season;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');
        for ($i = 1; $i < 50; $i++){
            $season = new Season();
            $year = $faker->year;
            $season->setYear($year);
            $description = $faker->sentence;
            $season->setDescription($description);
            $season->setProgram($this->getReference("program_" . rand(0,5)));
            $manager->persist($season);

            $this->addReference('season_' . $i, $season);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [ProgramFixtures::class];
    }

}