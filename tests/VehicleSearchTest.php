<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class VehicleSearchTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    public function testNoPlateProvided(): void
    {
        static::createClient()->request('GET', '/search');

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['message' => 'A vehicle license plate is required via the plate query string. e.g. `plate=AA%201234AB`.']);
        $this->assertJsonContains(['results' => []]);
    }

    public function testNoMatchesFound(): void
    {
        static::createClient()->request('GET', '/search?plate=MA03%20XHZ');

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['message' => 'No matches found.']);
        $this->assertJsonContains(['results' => []]);
    }
}
