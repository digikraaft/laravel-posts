<?php

namespace Digikraaft\LaravelPosts\Models;

use Digikraaft\LaravelPosts\Events\PostCreatedEvent;
use Digikraaft\LaravelPosts\Exceptions\InvalidArgumentException;
use Digikraaft\LaravelPosts\Exceptions\InvalidDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Rinvex\Categories\Traits\Categorizable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStatus\HasStatuses;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Tags\HasTags;
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    protected $guarded = [];
    public $translatable = ['title', 'slug', 'content'];

    use HasTags, HasStatuses, Categorizable, HasTranslations, HasTranslatableSlug;
//    use LogsActivity;

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
            ->saveSlugsTo(config('laravel-posts.save_slug_to', 'slug'))
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'content', 'meta']);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
//    public function getRouteKeyName()
//    {
//        return 'slug';
//    }

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
     * @param array $attributes
     * @return Post | \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     * @throws InvalidArgumentException
     */
    public static function create(string $title, string $content, array $attributes = []) : Post
    {
        if (Arr::exists($attributes, 'author')) {
            static::guardAgainstInvalidAuthorModel($attributes['author']);
        }

        $post = Post::query()->create([
            'uuid' => Str::uuid(),
            'title' => $title,
            'content' => $content,
            'author_id' => Arr::exists($attributes, 'author') ?  $attributes['author']->{$attributes['author']->getKeyName()}: null,
            'author_type' => Arr::exists($attributes, 'author') ? $attributes['author']->getMorphClass() : null,
            'meta' => $attributes['meta'] ?? null,
            'created_at' => $attributes['created_at'] ?? Carbon::now(),
            'updated_at' => $attributes['updated_at'] ?? Carbon::now(),
            'published_at' => $attributes['published_at'] ?? Carbon::now(),
        ]);

        event(new PostCreatedEvent($post));

        return $post;
    }

    public function author(): MorphTo
    {
        return $this->morphTo('author');
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

    public static function whereSlug(string $slug)
    {
        return static::query()->where('slug', $slug)->first();
    }

    public static function published(?Carbon $from = null, ?Carbon $to = null)
    {
        if (! $from && ! $to) {
            return Post::query()->where('published_at', '<=', Carbon::now())->get();
        }

        if ($from->greaterThan($to)) {
            throw InvalidDate::from();
        }

        return Post::query()->where('published_at', '<=', Carbon::now())->whereBetween(
            'published_at',
            [$from->toDateTimeString(), $to->toDateTimeString()]
        )->get();
    }

    public static function scheduled(?Carbon $from = null, ?Carbon $to = null)
    {
        if (! $from && ! $to) {
            return Post::query()->where('published_at', '>', Carbon::now())->get();
        }

        if ($from->greaterThan($to)) {
            throw InvalidDate::from();
        }

        return Post::query()->where('published_at', '>', Carbon::now())->whereBetween(
            'published_at',
            [$from->toDateTimeString(), $to->toDateTimeString()]
        )->get();
    }

    public static function byAuthor($author)
    {
        static::guardAgainstInvalidAuthorModel($author);

        return Post::query()->where('author_id', $author->{$author->getKeyName()})->where('author_type', $author->getMorphClass())->get();
    }
}
