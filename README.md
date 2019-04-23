# Laravel-Prismic
Automatic Eloquent models and better templating for Prismic

## Installation

You can install the package via composer:
```
composer require dromedar-design/laravel-prismic
```

Then you can configure it as a new database connection in `config/database.php`:
```php
'connections' => [

    'prismic' => [
        'driver' => 'prismic',
        'database' => 'https://XXX.cdn.prismic.io/api/v2',
        'cache' => false,
    ],
    
    // other connections
],
```

After this you only have to extend the Model shipped with the package and your Prismic api works like a usual Eloquent model.

```php
<?php

namespace App;

use DromedarDesign\Prismic\Model;

class Post extends Model
{
    //
}

```

## Relationships

Relationships work as you would expect it from regular Eloquent models.

## Accessing properties

There are three output modes:
- raw
- text
- html (this is the default)

```php
$post = \App\Post::first();

$title = $post->title; // By default it returns the html output

// Output: <h1>Hello, this is title</h1>
```

You can either access other modes directly or change the default.

```php
$title = $post->title->text;

// Output: Hello, this is title

$post->setPrismicMode('raw');
$title = $post->title;

/** Output:
 *  [
 *      {
 *          "type": "heading1",
 *          "text": "Hello, this is title",
 *          "spans": []
 *      }
 *  ]
 */
```

