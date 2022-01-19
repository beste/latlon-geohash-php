<?php

declare(strict_types=1);

namespace Beste\Geohash\Tests;

use Beste\Geohash;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GeohashTest extends TestCase
{
    /**
     * @dataProvider provideInvalidEncodeParameters
     */
    public function testItRejectsInvalidEncodeParameters(float $lat, float $lon, ?int $precision): void
    {
        $this->expectException(InvalidArgumentException::class);
        Geohash::encode($lat, $lon, $precision);
    }

    /**
     * @dataProvider provideInvalidAdjacentParameters
     */
    public function testItRejectsInvalidAdjacentParameters(string $geohash, string $direction): void
    {
        $this->expectException(InvalidArgumentException::class);
        Geohash::adjacent($geohash, $direction);
    }

    /**
     * @dataProvider provideInvalidGeohashes
     */
    public function testItRejectsInvalidDecodeParameters(string $geohash): void
    {
        $this->expectException(InvalidArgumentException::class);
        Geohash::decode($geohash);
    }

    /**
     * @dataProvider provideEncodeParameters
     */
    public function testItEncodesAndDecodesValues(float $lat, float $lon, ?int $precision): void
    {
        $encoded = Geohash::encode($lat, $lon, $precision);
        $decoded = Geohash::decode($encoded);

        $reEncoded = Geohash::encode($decoded['lat'], $decoded['lon'], $precision);

        self::assertSame($encoded, $reEncoded);
    }

    /**
     * @dataProvider provideGeohashes
     */
    public function testItHasNeighbours(string $geohash): void
    {
        $neighbours = Geohash::neighbours($geohash);

        self::assertSame($geohash, Geohash::neighbours($neighbours['n'])['s']);
        self::assertSame($geohash, Geohash::neighbours($neighbours['s'])['n']);
        self::assertSame($geohash, Geohash::neighbours($neighbours['e'])['w']);
        self::assertSame($geohash, Geohash::neighbours($neighbours['w'])['e']);
        self::assertSame($geohash, Geohash::neighbours($neighbours['ne'])['sw']);
        self::assertSame($geohash, Geohash::neighbours($neighbours['sw'])['ne']);
    }

    /**
     * @return iterable<array<float|int|null>>
     */
    public function provideEncodeParameters(): iterable
    {
        yield [52.51662414148685, 13.378843229770698, null];
        yield [52.51662414148685, 13.378843229770698, 1];
        yield [52.51662414148685, 13.378843229770698, 2];
        yield [52.51662414148685, 13.378843229770698, 3];
        yield [52.51662414148685, 13.378843229770698, 4];
        yield [52.51662414148685, 13.378843229770698, 5];
        yield [52.51662414148685, 13.378843229770698, 6];
        yield [52.51662414148685, 13.378843229770698, 7];
        yield [52.51662414148685, 13.378843229770698, 8];
        yield [52.51662414148685, 13.378843229770698, 9];
        yield [52.51662414148685, 13.378843229770698, 10];
        yield [52.51662414148685, 13.378843229770698, 11];
        yield [52.51662414148685, 13.378843229770698, 12];
        yield [0.0, 0.0, null];
        yield [-90.0, 180.0, null];
        yield [90.0, -180.0, null];
    }

    /**
     * @return iterable<array<float|int|null>>
     */
    public function provideInvalidEncodeParameters(): iterable
    {
        yield [-90.01, 0, null];
        yield [90.01, 0, null];
        yield [0, -180.01, null];
        yield [0, 180.01, null];
        yield [0, 0, 0];
        yield [0, 0, 13];
    }

    /**
     * @return iterable<string[]>
     */
    public function provideGeohashes(): iterable
    {
        yield ['u33db2q5t8k9'];
        yield ['u33db2q5t8k'];
        yield ['u33db2q5t8'];
        yield ['u33db2q5t'];
        yield ['u33db2q5'];
        yield ['u33db2q'];
        yield ['u33db2'];
        yield ['u33db'];
        yield ['u33d'];
        yield ['u33'];
        yield ['u3'];
        yield ['u'];
    }

    /**
     * @return iterable<string[]>
     */
    public function provideInvalidGeohashes(): iterable
    {
        yield ['a'];
    }

    /**
     * @return iterable<string, string[]>
     */
    public function provideInvalidAdjacentParameters(): iterable
    {
        yield 'invalid direction' => ['u', 'x'];
        yield 'empty geohash' => ['', 'n'];
        yield 'invalid geohash' => ['a', 'n'];
    }
}
