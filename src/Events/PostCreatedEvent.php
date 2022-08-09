<?php


namespace Digikraaft\LaravelPosts\Events;

use Digikraaft\LaravelPosts\Models\Post;
use Illuminate\Database\Eloquent\Model;

class PostCreatedEvent
{
    /** @var \Digikraaft\LaravelPosts\Models\Post */
    public Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}
