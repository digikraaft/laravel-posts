<?php

namespace Digikraaft\LaravelPosts\Tests;

use Digikraaft\LaravelPosts\LaravelPostsServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [LaravelPostsServiceProvider::class];
    }

    protected function setUpDatabase()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('custom_model_key_posts', function (Blueprint $table) {
            $table->id('id');
            $table->uuid('uuid')->unique();
            $table->text('title');
            $table->text('slug');
            $table->text('content');
            $table->string('model_type');
            $table->unsignedBigInteger('model_custom_fk');
            $table->index(['model_type', 'model_custom_fk']);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->dateTime('published_at');
            $table->softDeletes();
        });

        include_once __DIR__ . '/../database/migrations/create_posts_table.php.stub';

        (new \CreatePostsTable)->up();
    }
}
