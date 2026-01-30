<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Booking;
use App\Entity\Client;
use App\Entity\Song;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Clients
        $clients = [];
        // 5 Standard Clients
        for ($i = 1; $i <= 5; $i++) {
            $client = new Client();
            $client->setName("Standard User $i");
            $client->setEmail("standard$i@test.com");
            $client->setType(Client::TYPE_STANDARD);
            $manager->persist($client);
            $clients[] = $client;
        }

        // 5 Premium Clients
        for ($i = 1; $i <= 5; $i++) {
            $client = new Client();
            $client->setName("Premium User $i");
            $client->setEmail("premium$i@test.com");
            $client->setType(Client::TYPE_PREMIUM);
            $manager->persist($client);
            $clients[] = $client;
        }

        // Activities & Songs
        $activities = [];
        $types = [Activity::TYPE_BODYPUMP, Activity::TYPE_SPINNING, Activity::TYPE_CORE];
        
        for ($i = 1; $i <= 15; $i++) {
            $activity = new Activity();
            $type = $types[array_rand($types)];
            $activity->setType($type);
            $activity->setMaxParticipants(rand(5, 20)); // Random capacity
            
            // Random dates: spread over last month and next month
            $dateStart = new \DateTime();
            $days = rand(-30, 30);
            $dateStart->modify("$days days");
            $dateStart->setTime(rand(8, 20), 0, 0); // Open between 8am and 8pm
            
            $dateEnd = clone $dateStart;
            $dateEnd->modify("+45 minutes");
            
            $activity->setDateStart($dateStart);
            $activity->setDateEnd($dateEnd);

            $manager->persist($activity);
            $activities[] = $activity;

            // Add 3 Songs per Activity
            for ($s = 1; $s <= 3; $s++) {
                $song = new Song();
                $song->setName("Song $s for Activity $i");
                $song->setDurationSeconds(rand(180, 300));
                $activity->addSong($song); // Sets relation
                $manager->persist($song);
            }
        }

       
        

        $manager->flush();
    }
}
