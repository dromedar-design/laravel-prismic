<?php

namespace DromedarDesign\Prismic\Tests;

use DromedarDesign\Prismic\Model;

class Post extends Model
{
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
