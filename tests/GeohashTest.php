<?php

declare(strict_types=1);

namespace Beste\Geohash\Tests;

use Beste\Geohash;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @phpstan-import-type LatLonShape from Geohash
 * @phpstan-import-type BoundsShape from Geohash
 * @phpstan-import-type NeighboursShape from Geohash
 */
final class GeohashTest extends TestCase
{
    #[DataProvider('provideInvalidEncodeParameters')]
    public function testItRejectsInvalidEncodeParameters(float $lat, float $lon, ?int $precision): void
    {
        $this->expectException(InvalidArgumentException::class);
        Geohash::encode($lat, $lon, $precision);
    }

    /**
     * @param non-empty-string $direction
     */
    #[DataProvider('provideInvalidAdjacentParameters')]
    public function testItRejectsInvalidAdjacentParameters(string $geohash, string $direction): void
    {
        $this->expectException(InvalidArgumentException::class);
        Geohash::adjacent($geohash, $direction);
    }

    #[DataProvider('provideInvalidGeohashes')]
    public function testItRejectsInvalidDecodeParameters(string $geohash): void
    {
        $this->expectException(InvalidArgumentException::class);
        Geohash::decode($geohash);
    }

    /**
     * @param non-empty-string $geohash
     * @param LatLonShape $expected
     */
    #[DataProvider('provideDecodeValues')]
    public function testItDecodesGeohashes(string $geohash, array $expected): void
    {
        $decoded = Geohash::decode($geohash);

        self::assertSame($expected, $decoded);
    }

    /**
     * @param non-empty-string $geohash
     * @param BoundsShape $expected
     */
    #[DataProvider('provideBoundsValues')]
    public function testItReturnsBounds(string $geohash, array $expected): void
    {
        $bounds = Geohash::bounds($geohash);

        self::assertSame($expected, $bounds);
    }

    /**
     * @param non-empty-string $geohash
     * @param non-empty-string $direction
     * @param non-empty-string $expected
     */
    #[DataProvider('provideAdjacentValues')]
    public function testItCalculatesAdjacentGeohashes(string $geohash, string $direction, string $expected): void
    {
        $adjacent = Geohash::adjacent($geohash, $direction);

        self::assertSame($expected, $adjacent);
    }

    #[DataProvider('provideEncodeParameters')]
    public function testItEncodesAndDecodesValues(float $lat, float $lon, ?int $precision): void
    {
        $encoded = Geohash::encode($lat, $lon, $precision);
        $decoded = Geohash::decode($encoded);

        $reEncoded = Geohash::encode($decoded['lat'], $decoded['lon'], $precision);

        self::assertSame($encoded, $reEncoded);
    }

    #[DataProvider('provideGeohashes')]
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
    public static function provideEncodeParameters(): iterable
    {
        yield 'auto precision' => [52.51662414148685, 13.378843229770698, null];
        yield 'precision 1' => [52.51662414148685, 13.378843229770698, 1];
        yield 'precision 2' => [52.51662414148685, 13.378843229770698, 2];
        yield 'precision 3' => [52.51662414148685, 13.378843229770698, 3];
        yield 'precision 4' => [52.51662414148685, 13.378843229770698, 4];
        yield 'precision 5' => [52.51662414148685, 13.378843229770698, 5];
        yield 'precision 6' => [52.51662414148685, 13.378843229770698, 6];
        yield 'precision 7' => [52.51662414148685, 13.378843229770698, 7];
        yield 'precision 8' => [52.51662414148685, 13.378843229770698, 8];
        yield 'precision 9' => [52.51662414148685, 13.378843229770698, 9];
        yield 'precision 10' => [52.51662414148685, 13.378843229770698, 10];
        yield 'precision 11' => [52.51662414148685, 13.378843229770698, 11];
        yield 'precision 12' => [52.51662414148685, 13.378843229770698, 12];
        yield 'equator and prime meridian' => [0.0, 0.0, null];
        yield 'south pole and antimeridian' => [-90.0, 180.0, null];
        yield 'north pole and negative antimeridian' => [90.0, -180.0, null];
    }

    /**
     * @return iterable<array{0: float|int, 1: float|int, 2: non-negative-int|null}>
     */
    public static function provideInvalidEncodeParameters(): iterable
    {
        yield 'latitude too low' => [-90.01, 0, null];
        yield 'latitude too high' => [90.01, 0, null];
        yield 'longitude too low' => [0, -180.01, null];
        yield 'longitude too high' => [0, 180.01, null];
        yield 'precision too low' => [0, 0, 0];
        yield 'precision too high' => [0, 0, 13];
    }

    /**
     * @return iterable<string[]>
     */
    public static function provideGeohashes(): iterable
    {
        yield 'precision 12' => ['u33db2q5t8k9'];
        yield 'precision 11' => ['u33db2q5t8k'];
        yield 'precision 10' => ['u33db2q5t8'];
        yield 'precision 9' => ['u33db2q5t'];
        yield 'precision 8' => ['u33db2q5'];
        yield 'precision 7' => ['u33db2q'];
        yield 'precision 6' => ['u33db2'];
        yield 'precision 5' => ['u33db'];
        yield 'precision 4' => ['u33d'];
        yield 'precision 3' => ['u33'];
        yield 'precision 2' => ['u3'];
        yield 'precision 1' => ['u'];
    }

    /**
     * @return iterable<string[]>
     */
    public static function provideInvalidGeohashes(): iterable
    {
        yield 'contains invalid character' => ['a'];
    }

    /**
     * @return iterable<array{string, LatLonShape}>
     */
    public static function provideDecodeValues(): iterable
    {
        yield 'precision 4' => [
            'u33d',
            ['lat' => 52.47, 'lon' => 13.54],
        ];
        yield 'precision 12' => [
            'u33db2q5t8k9',
            ['lat' => 52.51662414, 'lon' => 13.37884331],
        ];
    }

    /**
     * @return iterable<array{non-empty-string, BoundsShape}>
     */
    public static function provideBoundsValues(): iterable
    {
        yield 'precision 4' => [
            'u33d',
            [
                'sw' => ['lat' => 52.3828125, 'lon' => 13.359375],
                'ne' => ['lat' => 52.55859375, 'lon' => 13.7109375],
            ],
        ];
        yield 'precision 12' => [
            'u33db2q5t8k9',
            [
                'sw' => ['lat' => 52.51662405207753, 'lon' => 13.378843143582344],
                'ne' => ['lat' => 52.516624219715595, 'lon' => 13.378843478858471],
            ],
        ];
    }

    /**
     * @return iterable<string, array{0: string, 1: non-empty-string}>
     */
    public static function provideInvalidAdjacentParameters(): iterable
    {
        yield 'invalid direction' => ['u', 'x'];
        yield 'empty geohash' => ['', 'n'];
        yield 'invalid geohash' => ['a', 'n'];
    }

    /**
     * @return iterable<array{non-empty-string, non-empty-string, non-empty-string}>
     */
    public static function provideAdjacentValues(): iterable
    {
        yield 'u33d north' => ['u33d', 'n', 'u33e'];
        yield 'u33d south' => ['u33d', 's', 'u339'];
        yield 'u33d east' => ['u33d', 'e', 'u33f'];
        yield 'u33d west' => ['u33d', 'w', 'u336'];
        yield 'ezs42 north' => ['ezs42', 'n', 'ezs48'];
        yield 'ezs42 south' => ['ezs42', 's', 'ezs40'];
        yield 'ezs42 east' => ['ezs42', 'e', 'ezs43'];
        yield 'ezs42 west (crosses boundary)' => ['ezs42', 'w', 'ezefr'];
        yield 'u33db2q5t8k9 north' => ['u33db2q5t8k9', 'n', 'u33db2q5t8kd'];
        yield 'u33db2q5t8k9 east' => ['u33db2q5t8k9', 'e', 'u33db2q5t8kc'];
    }
}
