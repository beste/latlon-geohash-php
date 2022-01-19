# Geohash

Library to convert a [geohash](https://en.wikipedia.org/wiki/Geohash) to/from a latitude/longitude point, and to
determine bounds of a geohash cell and find neighbours of a geohash.

This is a PHP implementation based on [chrisveness/latlon-geohash](https://github.com/chrisveness/latlon-geohash).
More information (with interactive conversion) at 
([www.movable-type.co.uk/scripts/geohash.html](https://www.movable-type.co.uk/scripts/geohash.html)).

## Usage

```php
use Beste\Geohash;

// encode latitude/longitude point to geohash of given precision (number of characters in resulting geohash);
// if precision is not specified, it is inferred from precision of latitude/longitude values.
Geohash::encode(float $lat, float $lon, ?int $precision = null)

// return { lat, lon } of centre of given geohash, to appropriate precision.
Geohash::decode(string $geohash)

// return { sw, ne } bounds of given geohash.
Geohash::bounds(string $geohash)

// return adjacent cell to given geohash in specified direction (n/s/e/w).
Geohash::adjacent(string $geohash, string $direction)

// return all 8 adjacent cells (n/ne/e/se/s/sw/w/nw) to given geohash.
Geohash::neighbours(string $geohash)
```

## Installation

```shell
composer require beste/latlon-geohash
```

## Running tests

```shell
composer run tests
```
