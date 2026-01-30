<?php

require __DIR__.'/vendor/autoload.php';

use App\Kernel;
use App\Entity\Client;
use App\Repository\BookingRepository;
use App\Repository\ClientRepository;

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

$repo = $container->get(BookingRepository::class);
$clientRepo = $container->get(ClientRepository::class);

$client = $clientRepo->find(1); // Juan Standard
if (!$client) {
    die("Client 1 not found\n");
}

echo "Client: " . $client->getName() . "\n";

// Scenario: Booking for Feb 1st (Sunday)
$activityDate = new \DateTime('2026-02-01 10:00:00');
echo "\n--- Activity Date: " . $activityDate->format('Y-m-d H:i:s l') . " ---\n";

$startOfWeek = (clone $activityDate)->modify('monday this week')->setTime(0, 0, 0);
$endOfWeek = (clone $activityDate)->modify('sunday this week')->setTime(23, 59, 59);

echo "Calculated Start (Monday): " . $startOfWeek->format('Y-m-d H:i:s l') . "\n";
echo "Calculated End (Sunday):   " . $endOfWeek->format('Y-m-d H:i:s l') . "\n";

$count = $repo->countBookingsForClientInWeek($client, $activityDate);
echo "Count of Bookings in this week: " . $count . "\n";

// List all bookings to see what's being counted
echo "All Bookings for Client:\n";
foreach ($client->getBookings() as $b) {
    echo " - ID: " . $b->getId() . " Date: " . $b->getActivity()->getDateStart()->format('Y-m-d H:i:s l') . "\n";
}
