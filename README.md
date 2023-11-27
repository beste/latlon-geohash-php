# Geohash

[![Current version](https://img.shields.io/packagist/v/beste/latlon-geohash.svg?logo=composer)](https://packagist.org/packages/beste/latlon-geohash)
[![Supported PHP version](https://img.shields.io/static/v1?logo=php&label=PHP&message=~7.4.0%20||%20~8.0.0%20||%20~8.1.0||%20~8.2.0&color=777bb4)](https://packagist.org/packages/beste/latlon-geohash)
[![Tests](https://github.com/beste/latlon-geohash-php/workflows/Tests/badge.svg)](https://github.com/beste/latlon-geohash-php/actions)
[![Sponsor](https://img.shields.io/static/v1?logo=GitHub&label=Sponsor&message=%E2%9D%A4&color=ff69b4)](https://github.com/sponsors/jeromegamez)

Library to convert a [geohash](https://en.wikipedia.org/wiki/Geohash) to/from a latitude/longitude point, and to
determine bounds of a geohash cell and find neighbours of a geohash.

This is a PHP implementation based on [chrisveness/latlon-geohash](https://github.com/chrisveness/latlon-geohash).
More information (with interactive conversion) at 
[www.movable-type.co.uk/scripts/geohash.html](https://www.movable-type.co.uk/scripts/geohash.html).

## Usage

```php
use Beste\Geohash;

// encode latitude/longitude point to geohash of given precision (number of
// characters in resulting geohash); if precision is not specified, it is
// inferred from precision of latitude/longitude values.
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
composer test
```
