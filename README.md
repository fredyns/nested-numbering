Nested Numbering
================

create nested numbering from PHP like...

```
1. main item.

  a. sub item 1.
 
  b. sub item 2.
  
2. more item

  a. more sub item .
```

or something like...

```
1. main item.

  1.a. sub item 1.
 
  1.b. sub item 2.
  
2. more item

  2.a. more sub item 1.
```



## Instalation
```
composer require "fredyns/nested-numbering":"dev-master"    
```
or just copy that one class defined in php file.


## Usage

```php
use fredyns\nestednumbering\NestedNumbering;

NestedNumbering::start(['1','a']);

echo NestedNumbering::newItem($level);
```


## Advance Config

```php
NestedNumbering::start([
    $numbering_type_level_1,
    $numbering_type_level_2,
    $numbering_type_level_3,
    $numbering_type_level_4,
    $numbering_type_level_5,
    'full' => true|false,         // generate full numering like *A.1.a*
    'indentation' => '    ',      // will add space before numbering. false for none.
]);
```

Numbering type:
- **A**: for uppercase alphabetic
- **a**: for lowercase alphabetic
- **I**: for uppercase roman
- **i**: for lowercase alphabetic
- **1**: for numeric

You can also specify numbering tail like ```'1)'```.  First char regarded as type, rest is tail.
For full numbering, tail set to dot (.)


## SIDE BONUS
There is converter from integer numeric to alphabetic & roman numbering.
```php
echo NestedNumbering::int2Char($integer, $uppercase);
echo NestedNumbering::int2Roman($integer);
```

