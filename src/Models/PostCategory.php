<?php


    namespace Digikraaft\LaravelPosts\Models;

    use Digikraaft\LaravelPosts\Exceptions\InvalidArgumentException;
    use Rinvex\Categories\Models\Category;

    class PostCategory extends Category
    {
        public static function create(array $attributes)
        {
            if(empty($attributes)){
                throw InvalidArgumentException::invalidPostCategoryArgument();
            }
            return app('rinvex.categories.category')->create($attributes);
        }
    }
