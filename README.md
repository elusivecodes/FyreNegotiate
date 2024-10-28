# FyreNegotiate

**FyreNegotiate** is a free, open-source HTTP negotiation library for *PHP*.


## Table Of Contents
- [Installation](#installation)
- [Methods](#methods)



## Installation

**Using Composer**

```
composer require fyre/negotiate
```

In PHP:

```php
use Fyre\Http\Negotiate;
```


## Methods

**Content**

Negotiate a content type.

- `$accepted` is a string representing the `Accept` header.
- `$supported` is an array containing the supported content values.
- `$strict` is a boolean indicating whether to not use a default fallback, and will default to *false*.

```php
Negotiate::content($accepted, $supported, $strict);
```

**Encoding**

Negotiate an encoding.

- `$accepted` is a string representing the `Accept-Encoding` header.
- `$supported` is an array containing the supported encoding values.

```php
Negotiate::encoding($accepted, $supported);
```

**Language**

Negotiate a language.

- `$accepted` is a string representing the `Accept-Language` header.
- `$supported` is an array containing the supported language values.

```php
Negotiate::language($accepted, $supported);
```