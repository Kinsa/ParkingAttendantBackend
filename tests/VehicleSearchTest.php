<?php

namespace App\Tests;

use App\Entity\Vehicle;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class VehicleSearchTest extends ApiTestCase
{
    private static $PLATE = 'AA 1234AB';
    private static $TIME_IN = '2025-11-10 02:36:00';

    protected static ?bool $alwaysBootKernel = true;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Boot kernel to get container
        self::bootKernel();

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $vehicle = new Vehicle();
        $vehicle->setLicensePlate(self::$PLATE);
        $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', self::$TIME_IN);
        $vehicle->setTimeIn($date);

        $entityManager->persist($vehicle);
        $entityManager->flush();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        // Clear the table after all tests
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $conn = $entityManager->getConnection();
        $sql = 'DELETE FROM vehicle';
        $conn->executeQuery($sql);
    }

    public function testNoPlateProvided(): void
    {
        static::createClient()->request('GET', '/search');

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['message' => 'A vehicle license plate is required via the plate query string. e.g. `plate=AA%201234AB`.']);
        $this->assertJsonContains(['results' => []]);
    }

    public function testNoMatchesFound(): void
    {
        static::createClient()->request('GET', '/search', [
            'query' => ['plate' => self::$PLATE . 'XYZ']
        ]);

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['message' => 'No results found.']);
        $this->assertJsonContains(['results' => []]);
    }

    public function testMatchFound(): void
    {
        static::createClient()->request('GET', '/search', [
            'query' => ['plate' => self::$PLATE]
        ]);
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['message' => '1 result found.']);
        $this->assertJsonContains([
            'results' => [
                [
                    'license_plate' => self::$PLATE,
                    'time_in' => self::$TIME_IN
                ]
            ]
        ]);
    }
}
