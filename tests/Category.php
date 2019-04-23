<?php

namespace DromedarDesign\Prismic\Tests;

use DromedarDesign\Prismic\Model;

class Category extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
