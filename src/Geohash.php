<?php

declare(strict_types=1);

namespace Beste;

use InvalidArgumentException;

/**
 * Geohash: Gustavo Niemeyer's geocoding system.
 *
 * Based on the JS implementation by Chris Veness
 *
 * @see https://github.com/chrisveness/latlon-geohash
 *
 * @phpstan-type BoundsShape array{
 *      sw: array{lat: float, lon: float},
 *      ne: array{lat: float, lon: float}
 *  }
 *
 * @phpstan-type NeighboursShape array{
 *      n: non-empty-string,
 *      ne: non-empty-string,
 *      e: non-empty-string,
 *      se: non-empty-string,
 *      s: non-empty-string,
 *      sw: non-empty-string,
 *      w: non-empty-string,
 *      nw: non-empty-string,
 *  }
 *
 * @phpstan-type LatLonShape array{lat: float, lon: float}
 */
final class Geohash
{
    private const base32 = '0123456789bcdefghjkmnpqrstuvwxyz';

    /**
     * @throws InvalidArgumentException
     */
    public static function encode(float $lat, float $lon, ?int $precision = null): string
    {
        $latMin = -90.0;
        $latMax = 90.0;
        $lonMin = -180.0;
        $lonMax = 180.0;
        $precisionMin = 1;
        $precisionMax = 12;

        if ($lat < $latMin || $lat > $latMax) {
            throw new InvalidArgumentException("The latitude must be between $latMin and $latMax");
        }

        if ($lon < $lonMin || $lon > $lonMax) {
            throw new InvalidArgumentException("The longitude must be between $lonMin and $lonMax");
        }

        if (($precision !== null) && ($precision < $precisionMin || $precision > $precisionMax)) {
            throw new InvalidArgumentException("The precision must be between $precisionMin and $precisionMax");
        }

        if ($precision === null) {
            $latDecimals = self::numberOfDecimals($lat);
            $lonDecimals = self::numberOfDecimals($lon);

            foreach(range(1, 12) as $targetPrecision) {
                $hash = self::encode($lat, $lon, $targetPrecision);
                $position = self::decode($hash);

                $latPosition = $position['lat'];
                $lonPosition = $position['lon'];

                $latPosition = (float) number_format($latPosition, $latDecimals);
                $lonPosition = (float) number_format($lonPosition, $lonDecimals);

                if ($lat === $latPosition && $lon === $lonPosition) {
                    return $hash;
                }

                $precision = 12; // Set to maximum
            }
        }

        $idx = 0; // index into base32 map
        $bit = 0; // each char holds 5 bits
        $evenBit = true;
        $geohash = '';
        $geohashLength = 0;

        while($geohashLength < $precision) {
            if ($evenBit) {
                // bisect E-W longitude
                $lonMid = ($lonMin + $lonMax) / 2;

                if ($lon >= $lonMid) {
                    $idx = ($idx * 2) + 1;
                    $lonMin = $lonMid;
                } else {
                    $idx *= 2;
                    $lonMax = $lonMid;
                }

            } else {
                // bisect N-S latitude
                $latMid = ($latMin + $latMax) / 2;

                if ($lat >= $latMid) {
                    $idx = $idx * 2 + 1;
                    $latMin = $latMid;
                } else {
                    $idx *= 2;
                    $latMax = $latMid;
                }
            }

            $evenBit = !$evenBit;

            if (++$bit === 5) {
                // 5 bits gives us a character: append it and start over
                $geohash .= self::base32[$idx];
                $geohashLength++;
                $bit = 0;
                $idx = 0;
            }
        }

        return $geohash;
    }

    /**
     * @param string $geohash
     *
     * @return LatLonShape
     */
    public static function decode(string $geohash): array
    {
        $bounds = self::bounds($geohash);

        $latMin = $bounds['sw']['lat'];
        $lonMin = $bounds['sw']['lon'];
        $latMax = $bounds['ne']['lat'];
        $lonMax = $bounds['ne']['lon'];

        // cell centre
        $lat = ($latMin + $latMax) / 2;
        $lon = ($lonMin + $lonMax) / 2;

        // round to close to centre without excessive precision: ⌊2-log10(Δ°)⌋ decimal places
        $latPrecision = floor(2 - log10($latMax - $latMin));
        $lonPrecision = floor(2 - log10($lonMax - $lonMin));

        $latString = number_format($lat, (int) $latPrecision);
        $lonString = number_format($lon, (int) $lonPrecision);

        return [
            'lat' => (float) $latString,
            'lon' => (float) $lonString,
        ];
    }

    /**
     * @return BoundsShape
     */
    public static function bounds(string $geohash): array
    {
        $geohash = strtolower($geohash);

        self::assertValidGeohash($geohash, true);

        $evenBit = true;
        $latMin = -90.0;
        $latMax = 90.0;
        $lonMin = -180.0;
        $lonMax = 180.0;

        $geohashLength = strlen($geohash);

        for ($i = 0; $i < $geohashLength; $i++) {
            $char = $geohash[$i];
            $idx = strpos(self::base32, $char);

            for ($n = 4; $n >= 0; $n--) {
                $bitN = $idx >> $n & 1;

                if ($evenBit) {
                    // longitude
                    $lonMid = ($lonMin + $lonMax) / 2;
                    if ($bitN === 1) {
                        $lonMin = $lonMid;
                    } else {
                        $lonMax = $lonMid;
                    }
                } else {
                    // latitude
                    $latMid = ($latMin + $latMax) / 2;

                    if ($bitN === 1) {
                        $latMin = $latMid;
                    } else {
                        $latMax = $latMid;
                    }
                }

                $evenBit = !$evenBit;
            }
        }

        return [
            'sw' => ['lat' => $latMin, 'lon' => $lonMin],
            'ne' => ['lat' => $latMax, 'lon' => $lonMax],
        ];
    }

    public static function adjacent(string $geohash, string $direction): string
    {
        $geohash = strtolower($geohash);
        $direction = strtolower($direction);

        self::assertValidGeohash($geohash, false);
        self::assertValidDirection($direction);

        $neighbour = [
            'n' => [ 'p0r21436x8zb9dcf5h7kjnmqesgutwvy', 'bc01fg45238967deuvhjyznpkmstqrwx' ],
            's' => [ '14365h7k9dcfesgujnmqp0r2twvyx8zb', '238967debc01fg45kmstqrwxuvhjyznp' ],
            'e' => [ 'bc01fg45238967deuvhjyznpkmstqrwx', 'p0r21436x8zb9dcf5h7kjnmqesgutwvy' ],
            'w' => [ '238967debc01fg45kmstqrwxuvhjyznp', '14365h7k9dcfesgujnmqp0r2twvyx8zb' ],
        ];

        $border = [
            'n' =>  [ 'prxz',     'bcfguvyz' ],
            's' =>  [ '028b',     '0145hjnp' ],
            'e' =>  [ 'bcfguvyz', 'prxz'     ],
            'w' =>  [ '0145hjnp', '028b'     ],
        ];

        $lastChar = substr($geohash, -1);
        $parent = substr($geohash, 0, -1);

        $type = strlen($geohash) % 2;

        // check for edge-cases which don't share common prefix
        if ($parent !== '' && strpos($border[$direction][$type], $lastChar) !== false) {
            $parent = self::adjacent($parent, $direction);
        }

        // append letter for direction to parent
        return $parent . self::base32[strpos($neighbour[$direction][$type], $lastChar)];
    }

    /**
     * @param string $geohash
     * @return NeighboursShape
     */
    public static function neighbours(string $geohash): array
    {
        $n = self::adjacent($geohash, 'n');
        $s = self::adjacent($geohash, 's');

        return [
            'n' => $n,
            'ne' => self::adjacent($n, 'e'),
            'e' => self::adjacent($geohash, 'e'),
            'se' => self::adjacent($s, 'e'),
            's' => $s,
            'sw' => self::adjacent($s, 'w'),
            'w' => self::adjacent($geohash, 'w'),
            'nw' => self::adjacent($n, 'w'),
        ];
    }

    private static function numberOfDecimals(float $value): int
    {
        $string = number_format($value, 14);
        $point = strpos($string, '.');

        assert($point !== false);

        $decimals = substr($string, $point + 1);
        assert(ctype_digit($decimals));

        $decimals = rtrim($decimals, '0');

        return strlen($decimals);
    }

    private static function assertValidGeohash(string $geohash, bool $allowEmpty): void
    {
        if ($geohash === '' && $allowEmpty === false) {
            throw new InvalidArgumentException('Invalid geohash');
        }

        foreach (str_split($geohash) as $char) {
            if (strpos(self::base32, $char) === false) {
                throw new InvalidArgumentException('Invalid geohash');
            }
        }
    }

    private static function assertValidDirection(string $direction): void
    {
        if (!in_array($direction, ['n', 's', 'e', 'w'], true)) {
            throw new InvalidArgumentException('Invalid direction');
        }
    }
}
