<?php

namespace Digikraaft\LaravelPosts\Models;

use Carbon\Carbon;
use Digikraaft\LaravelPosts\Events\PostCreatedEvent;
use Digikraaft\LaravelPosts\Exceptions\InvalidArgumentException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Rinvex\Categories\Traits\Categorizable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStatus\HasStatuses;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Tags\HasTags;
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    protected $guarded = [];

    use HasTags, HasStatuses, Categorizable, LogsActivity, HasTranslations, HasTranslatableSlug;

    //author class - traits

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(config('laravel-posts.generate_slug_from', 'title'))
            ->saveSlugsTo(config('laravel-posts.save_slug_to', 'slug'));
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return config('laravel-posts.posts_table_name', 'dk-posts');
    }

    /**
     * @param string $title
     * @param string $content
     * @param array $params
     * @return Post | \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     * @throws InvalidArgumentException
     */
    public static function create(string $title, string $content, ... $params) : Post
    {
        if(! isset($params['author'])){
            static::guardAgainstInvalidAuthorModel($params['author']);
        }

        $defaultColumns = [
            'author',
            'meta',
            'published_at',
            'created_at',
            'updated_at',
        ];
        $customData = Arr::except($params, $defaultColumns);

        $post = Post::query()->create([
            'uuid' => Str::uuid(),
            'title' => $title,
            'content' => $content,
            'author_id' => $params['author']? $params['author']->getKeyName() : null,
            'author_type' => $params['author']? $params['author']->getMorphClass() : null,
            'meta' => $params['meta'] ?? null,
            'created_at' => $params['created_at'] ?? Carbon::now(),
            'updated_at' => $params['updated_at'] ?? Carbon::now(),
            'published_at' => $params['published_at'] ?? Carbon::now(),
            $customData,
        ]);

        event(new PostCreatedEvent($post));
        return $post;
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    private static function guardAgainstInvalidAuthorModel($author)
    {

        if (! is_a($author, Model::class, true)) {
            throw InvalidArgumentException::invalidAuthorModel($author);
        }
    }

    public function readingTime(bool $stripTags = true)
    {
        $content = $stripTags? strip_tags($this->content) : $this->content;
        return Str::readingTime($content);
    }

    public function getActivitylogOptions(): LogOptions
    {
        // TODO: Implement getActivitylogOptions() method.
    }
}
