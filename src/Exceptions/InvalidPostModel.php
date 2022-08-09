<?php

namespace Digikraaft\LaravelPosts\Exceptions;

use Exception;

class InvalidPostModel extends Exception
{
    public static function create(string $model): self
    {
        return new self("The model `{$model}` is invalid. A valid model must extend the model \Digikraaft\Models\Post.");
    }
}
