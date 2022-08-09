<?php


namespace Digikraaft\LaravelPosts\Events;

use Digikraaft\LaravelPosts\Models\Post;

class PostUpdatedEvent
{
    /** @var \Digikraaft\LaravelPosts\Models\Post */
    public Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}
