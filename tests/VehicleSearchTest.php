<?php

namespace App\Tests;

use App\Entity\Vehicle;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class VehicleSearchTest extends ApiTestCase
{
    private static $PLATE = 'AA 1234AB';
    private static $SIMILAR_PLATE = 'AA I2BAAB';
    private static $TIME_IN;

    protected static ?bool $alwaysBootKernel = true;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Initialize TIME_IN with current time minus 1 hour
        $date = new \DateTimeImmutable('-1 hour');
        self::$TIME_IN = $date->format('Y-m-d H:i:s');

        // Boot kernel to get container
        self::bootKernel();

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $vehicle = new Vehicle();
        $vehicle->setLicensePlate(self::$PLATE);
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

    /**
     * Test that a request without the plate parameter returns a bad request response.
     */
    public function testNoPlateProvided(): void
    {
        static::createClient()->request('GET', '/search');

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['message' => 'A vehicle license plate is required via the plate query string. e.g. `plate=AA%201234AB`.']);
        $this->assertJsonContains(['results' => []]);
    }

    /**
     * Test that a license plate search for a plate not in the database 
     * returns a not found response.
     */
    public function testNoMatchesFound(): void
    {
        static::createClient()->request('GET', '/search', [
            'query' => ['plate' => self::$PLATE . 'XYZ']
        ]);

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['message' => 'No results found.']);
        $this->assertJsonContains(['results' => []]);
    }

    /**
     * Test that a license plate search returns matching results.
     *
     * Searches for an exact match of a plate and expects to find
     * the full matching vehicle record.
     */
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

    /**
     * Test that a similar license plate search returns matching results.
     */
    public function testSimilarMatchFound(): void
    {
        static::createClient()->request('GET', '/search', [
            'query' => ['plate' => self::$SIMILAR_PLATE]
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

    /**
     * Test that a partial license plate search returns matching results.
     */
    public function testPartialSimilarMatchFound(): void
    {
        static::createClient()->request('GET', '/search', [
            'query' => ['plate' => substr(self::$PLATE, 0, 8)]
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
