<?php

namespace Digikraaft\LaravelPosts\Models;

use Digikraaft\LaravelPosts\Events\PostCreatedEvent;
use Digikraaft\LaravelPosts\Exceptions\InvalidArgumentException;
use Digikraaft\LaravelPosts\Exceptions\InvalidDate;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
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
    public $translatable = ['title', 'slug', 'content'];

    use HasTags, HasStatuses, Categorizable, LogsActivity, HasTranslations, HasTranslatableSlug;

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
    public function getRouteKeyName()
    {
        return 'slug';
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
        if(isset($params['author'])){
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

        if(empty($customData)){
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
            ]);
        }else{
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
        }
//        $post = Post::query()->where('uuid')
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

    public static function whereSlug(string $slug)
    {
        return static::query()->where('slug', $slug)->first();
    }

    public static function published(?Carbon $from = null, ?Carbon $to = null)
    {
        if (!$from && !$to) {
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
        if (!$from && !$to) {
            return Post::query()->where('published_at', '>=', Carbon::now())->get();
        }

        if ($from->greaterThan($to)) {
            throw InvalidDate::from();
        }

        return Post::query()->where('published_at', '>=', Carbon::now())->whereBetween(
            'published_at',
            [$from->toDateTimeString(), $to->toDateTimeString()]
        )->get();
    }

    public static function byAuthor($author)
    {
        static::guardAgainstInvalidAuthorModel($author);

        return Post::query()->where('author_id', $author->getKeyName())->where('author_type', $author->getMorphClass())->get();
    }

}
