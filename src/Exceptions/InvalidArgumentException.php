<?php

    namespace Digikraaft\LaravelPosts\Exceptions;

    use Illuminate\Database\Eloquent\Model;

    class InvalidArgumentException extends \Exception
    {
        public static function invalidPostArguments(array $expectedParameters)
        {
            return new static("Invalid Argument. Parameters can only be any of the following: ". implode(', ', $expectedParameters));
        }

        public static function invalidAuthorModel(string $author)
        {
            return new static("Invalid Author model. Author model must be an Eloquent model and therefore must extend ". Model::class. "The model used is .". $author);
        }

        public static function invalidPostCategoryArgument()
        {
            return new static ("Invalid Post Category");
        }
    }
