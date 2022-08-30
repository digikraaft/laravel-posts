<?php

namespace Digikraaft\LaravelPosts;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class LaravelPostsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishables();
        $this->addReadingTimeMacro();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-posts.php', 'laravel-posts');
    }

    protected function registerPublishables(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
        }

        if (! class_exists('CreatePostsTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/create_posts_table.php.stub' => database_path('migrations/'.$timestamp.'_create_posts_table.php'),
            ], 'migrations');
        }

        $this->publishes([
            __DIR__ . '/../config/laravel-posts.php' => config_path('laravel-posts.php'),
        ], 'config');
    }

    protected function addReadingTimeMacro()
    {
        Str::macro('readingTime', function (...$text) {
            $totalWords = str_word_count(implode(' ', $text));

            $minutes = ceil($totalWords / 200);
            $minutes = max(1, $minutes);

            return ($minutes > 1) ? $minutes . ' minutes' : $minutes . ' minute';
        });
    }
}
