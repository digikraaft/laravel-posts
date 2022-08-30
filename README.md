# Add basic Blog/Post functionality to your Laravel app.
![tests](https://github.com/digikraaft/laravel-posts/workflows/tests/badge.svg?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/digikraaft/laravel-posts/badges/build.png?b=master)](https://scrutinizer-ci.com/g/digikraaft/laravel-posts/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/digikraaft/laravel-posts/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/digikraaft/laravel-posts/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/digikraaft/laravel-posts/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

## Why this package?
This package provides a simple blog functionality for use in a Laravel app. This is an opinionated package. We have had to implement blog functionalities in different Laravel projects and decided to abstract this into a package which can be easily used across our projects. If you find it useful and meets your needs, by all means please use it. Suggested improvements are welcome.

## Notes on dependencies
This package uses the following dependencies. Please ensure to follow the installation and usage instructions from their respective repositories:

- [Laravel Categories](https://github.com/rinvex/laravel-categories) by [Rinvex](https://github.com/rinvex)
- [Laravel Sluggagle](https://github.com/spatie/laravel-sluggable) by [Spatie](https://github.com/spatie)
- [Laravel Translatable](https://github.com/spatie/laravel-translatable) by [Spatie](https://github.com/spatie)
- [Laravel ActivityLog](https://github.com/spatie/laravel-activitylog) by [Spatie](https://github.com/spatie)
- [Laravel Model Status](https://github.com/spatie/laravel-model-status) by [Spatie](https://github.com/spatie)
- [Laravel Tags](https://github.com/spatie/laravel-tags) by [Spatie](https://github.com/spatie)

## Usage 
```php
use Digikraaft\LaravelPosts\Models\Post;

// Create a post
$title = 'My first Post';
$content = 'Not really sure of what to write here! Can I get some help please?';
Post::create($title, $content);

// Create post with more attributes
$title = 'My Second Post';
$content = 'I may just need to get the services of a content writer. Thoughts?';
$author = User::find(1);
$additionalDetails = [
    'author' => $author,
    'created_at' => \Illuminate\Support\Carbon::now(),
    'published_at' => \Illuminate\Support\Carbon::now(),
];
$post = Post::create($title, $content, $additionalDetails);
```
Please note that the author `$author` must be an eloquent model otherwise an exception `Digikraaft\LaravelPosts\Exceptions\InvalidArgumentException` will be thrown.

## Installation

You can install the package via composer:

```bash
composer require digikraaft/laravel-posts
```
You must publish the migration with:
```bash
php artisan vendor:publish --provider="Digikraaft\LaravelPosts\LaravelPostsServiceProvider" --tag="migrations"
```
Run the migration to publish the posts table with:
```bash
php artisan migrate
```
You can optionally publish the config-file with:
```bash
php artisan vendor:publish --provider="Digikraaft\LaravelPosts\LaravelPostsServiceProvider" --tag="config"
```
The content of the file that will be published to `config/laravel-posts.php`:
```php
return [
    /*
     * The name of the column which holds the ID of the model that is the author of the posts.
     *
     * Only change this value if you have set a different name in the migration for the posts table.
     */
    'model_primary_key_attribute' => 'model_id',

    /*
     * The table name where your posts will be stored.
     */
    'posts_table_name' => 'dk_posts',

    /*
     * The column name where posts slug should be generated from
     */
    'generate_slug_from' => 'title',

     /*
     * The column name where slugs should be saved to
     */
    'save_slug_to' => 'slug',

];
```

## Usage
### Create a post
```php
use Digikraaft\LaravelPosts\Models\Post;

// create a post
$title = 'My first Post';
$content = 'Not really sure of what to write here! Can I get some help please?';
Post::create($title, $content);
```

### Create post with attributes
```php
use Digikraaft\LaravelPosts\Models\Post;

// create post with more attributes
$title = 'My Second Post';
$content = 'I may just need to get the services of a content writer. Thoughts?';
$author = User::find(1);
$additionalDetails = [
    'author' => $author,
    'updated_at' => \Illuminate\Support\Carbon::now(),
    'created_at' => \Illuminate\Support\Carbon::now(),
    'published_at' => \Illuminate\Support\Carbon::now(),
];
$post = Post::create($title, $content, $additionalDetails);
```
Please note that the author `$author` must be an eloquent model otherwise an exception `Digikraaft\LaravelPosts\Exceptions\InvalidArgumentException` will be thrown.

All attributes are optional. If you need to add additional information about a post, you can use the `meta` attribute like this:
```php
use Digikraaft\LaravelPosts\Models\Post;


$title = "Post title";
$content = "Post content";

$additionalDetails = [
    'meta' => [
        'seo_title' => 'SEO Title'
    ]   
];
Post::create($title, $content, $additionalDetails);
```

### Create post with custom attributes
If you need to save specific information on a post, you can do it by using custom attributes.
```php
use Digikraaft\LaravelPosts\Models\Post;

// Create post with custom attributes
$title = 'My Third Post';
$content = 'At this point, it amazes me why I can\'t seem to find the right words!';
$author = User::find(1);
$customDetails = [
    'custom_key' => $author,
];
$post = Post::create($title, $content, $customDetails);
```
Please ensure you have added the attributes as columns to the posts migration otherwise, Laravel will throw an exception.

### Retrieving Posts
The Post model is a normal eloquent model so all eloquent methods and query builders can be used in retrieving posts.

```php
use Digikraaft\LaravelPosts\Models\Post;

//Retrieve post by id
Post::find(1);

//Retrieve post by slug
Post::where('slug', 'post-title-1')->get();

//Retrieve post instance by slug
$post = Post::whereSlug('post-title-1');

//Retrieve all published posts. Published posts are posts where published_at date is today or in the past
Post::published();

//Retrieve all published posts within a period
$from = now()->subMonth();
$to = now();
Post::published($from, $to);
//Note that an `InvalidDate` exception will be thrown if the $from date is later than the $to


//Retrieve all scheduled posts. Scheduled posts are posts with published_at date in the future
Post::scheduled();

//Retrieve all scheduled posts within a period
$from = now();
$to = now()->addMonth();
Post::scheduled($from, $to);

//retrieve all posts by author
$author = User::find(1);
Post::byAuthor($author);
```
For more ways to retrieve posts and use the dependencies, check usage instructions of the following packages:

- [Posts Categories](https://github.com/rinvex/laravel-categories)
- [Posts Translations](https://github.com/spatie/laravel-translatable)
- [Laravel ActivityLog](https://github.com/spatie/laravel-activitylog)
- [Post Status](https://github.com/spatie/laravel-model-status)
- [Post Tags](https://github.com/spatie/laravel-tags)
- [Post Slug](https://github.com/spatie/laravel-sluggable)

#### Retrieving basic Post Stats
You can get the reading time of a post:
```php
use Digikraaft\LaravelPosts\Models\Post;

$post = Post::find(1);
$post->readingTime(); // returns reading time in minutes
```

### Using Slug
This package uses [Laravel Sluggable](https://github.com/spatie/laravel-sluggable) by [Spatie](https://github.com/spatie) to handle categories. Please check their usage and installation instructions.

### Using Categories
This package uses [Laravel Categories](https://github.com/rinvex/laravel-categories) by [Rinvex](https://github.com/rinvex) to handle categories. Please check the usage and installation instructions. This package however has helper classes to use categories:

```php
use Digikraaft\LaravelPosts\Models\Post;
use Digikraaft\LaravelPosts\Models\PostCategory;

//create categories
$attributes = ['name' => 'News', 'slug' => 'news'];
PostCategory::create($attributes);

//attach categories
$post = Post::find(1);
$post->attachCategories(['first-category', 'second-category']);

// Get attached categories collection
$post->categories;

// Get attached categories query builder
$post->categories();
```

### Using Tags
This package uses [Laravel Tags](https://github.com/spatie/laravel-tags) by [Spatie](https://github.com/spatie) to handle tags. Please check the usage and installation instructions. Here are a few ways to use:

```php
use Digikraaft\LaravelPosts\Models\Post;

//attach tags
$post = Post::find(1);

//attach single tag
$post->attachTag('first tag');

//multiple tags
$tags = ['second tag', 'third tag', 'fourth tag', 'fifth tag'];
$post->attachTags($tags);

$post->attachTags(['sixth_tag','seventh_tag'],'some_type');


// detaching tags
$post->detachTags('third tag');
$post->detachTags(['fourth tag', 'fifth tag']);

// Get all post tags
$post->tags;

// retrieving tags with a type
$post->tagsWithType('some_type'); 

// syncing tags
$post->syncTags(['first tag', 'second tag']); // all other tags on this model will be detached

// retrieving post that have any of the given tags
Post::withAnyTags(['first tag', 'second tag'])->get();

// retrieve posts that have all of the given tags
Post::withAllTags(['first tag', 'second tag'])->get();
```
For more tag usage, checkout the [documentation](https://github.com/spatie/laravel-tags)


### Events
The `Digikraaft\LaravelPosts\Events\PostCreatedEvent` event will be dispatched when a post has been created. You can listen to this event and take necessary actions.
An instance of the post will be passed to the event class and can be accessed for use:
```php
namespace Digikraaft\LaravelPosts\Events;

use Digikraaft\LaravelPosts\Models\Post;

class PostCreatedEvent
{
    /** @var \Digikraaft\LaravelPosts\Models\Post */
    public Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}
```

### Custom model and migration
You can change the model used by extending the `Digikraaft\LaravelPosts\Models\Post` class.

You can also change the column name used in the `dk_posts` table 
(default is `model_id`) when using a custom migration. If this is the case, also change the `model_primary_key_attribute` key of the `laravel-posts` config file.

## Testing
Use the command below to run your tests:
```bash
composer test
```

## More Good Stuff
Check [here](https://github.com/digikraaft) for more awesome free stuff!

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security
If you discover any security related issues, please email hello@digikraaft.ng instead of using the issue tracker.

## Credits
- [Tim Oladoyinbo](https://github.com/timoladoyinbo)
- [All Contributors](../../contributors)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
