<?php

    namespace Digikraaft\LaravelPosts\Exceptions;

    use Illuminate\Database\Eloquent\Model;

    class InvalidArgumentException extends \Exception
    {
        public static function invalidPostArguments(array $expectedParameters)
        {
            return new static("Invalid Argument. Parameters can only be any of the following: ". implode(', ', $expectedParameters));
        }

        public static function invalidAuthorModel()
        {
            return new static("Invalid Author model. Author model must be an Eloquent model and therefore must extend ". Model::class);
        }
    }
