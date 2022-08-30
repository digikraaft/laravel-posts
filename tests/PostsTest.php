<?php


namespace Digikraaft\LaravelPosts\Tests;

use Digikraaft\LaravelPosts\Events\PostCreatedEvent;
use Digikraaft\LaravelPosts\Exceptions\InvalidArgumentException;
use Digikraaft\LaravelPosts\Models\Post;
use Digikraaft\LaravelPosts\Tests\Models\InvalidAuthorModel;
use Digikraaft\LaravelPosts\Tests\Models\TestAuthorModel;
use Digikraaft\LaravelPosts\Tests\Models\TestModel;
use Illuminate\Support\Facades\Event;

class PostsTest extends TestCase
{
    /** @var TestModel */
    protected TestModel $testModel;
    protected TestAuthorModel $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = TestAuthorModel::create([
            'name' => 'Test User',
        ]);
    }

    /**
     * @test
     * @throws \Digikraaft\LaravelPosts\Exceptions\InvalidArgumentException
     */
    public function it_can_create_post()
    {
//        Event::fake();
        $post = Post::create("First Post", "Not really sure of what to write here! Can I get some help please?");
        $this->assertEquals('First Post', $post->title);
        $this->assertEquals('first-post', $post->slug);
//        Event::assertDispatched(PostCreatedEvent::class);
    }

    /**
     * @test
     */
    public function it_can_create_post_with_attributes()
    {
        $meta = [
            "meta" => [
                "info" => "some meta text"
            ]
        ];
        $post = Post::create("Another Post", "Not really sure of what to write here! Can I get some help please?", $meta);
        $this->assertEquals('Another Post', $post->title);
        $this->assertEquals('another-post', $post->slug);
        $this->assertIsArray($post->meta);
    }

    /**
     * @test
     */
    public function it_can_create_post_with_author()
    {
        $meta = [
            'author' => $this->author
        ];
        $post = Post::create("Another Post", "Not really sure of what to write here! Can I get some help please?", $meta);
        $this->assertEquals('Another Post', $post->title);
        $this->assertEquals('another-post', $post->slug);
        $this->assertEquals($this->author->name, $post->author->name);
    }

    /**
     * @test
     */
    public function it_throws_error_when_invalid_author_is_used()
    {
        $meta = [
            'author' => InvalidAuthorModel::class
        ];
        $this->expectException(InvalidArgumentException::class);
        Post::create("Another Post", "Not really sure of what to write here! Can I get some help please?", $meta);
    }

    /**
     * @test
     */
    public function it_can_get_published_posts()
    {
        $meta = [
            "published_at" => now()->addDay()
        ];
        $post = Post::create("Another Post", "Not really sure of what to write here! Can I get some help please?", $meta);
        $this->assertEquals('Another Post', $post->title);
        $this->assertEquals('another-post', $post->slug);
        $this->assertEquals(0, Post::published()->count());

        $meta = [
            "published_at" => now()
        ];
        Post::create("Previous Post", "Some post in the past", $meta);
        $this->assertEquals(1, Post::published()->count());

        $meta = [
            "published_at" => now()->subDays(2)
        ];
        Post::create("Future Post", "Some post in the past", $meta);
        $this->assertEquals(2, Post::published()->count());
    }

    /**
     * @test
     */
    public function it_can_get_scheduled_posts()
    {
        $meta = [
            "published_at" => now()->addDay()
        ];
        $post = Post::create("Another Post", "Not really sure of what to write here! Can I get some help please?", $meta);
        $this->assertEquals('Another Post', $post->title);
        $this->assertEquals('another-post', $post->slug);
        $this->assertEquals(1, Post::scheduled()->count());

        $meta = [
            "published_at" => now()
        ];
        Post::create("Previous Post", "Some post in the past", $meta);
        $this->assertEquals(1, Post::scheduled()->count());

        $meta = [
            "published_at" => now()->subDays(2)
        ];
        Post::create("Way back Post", "Some post in the past", $meta);
        $this->assertEquals(1, Post::scheduled()->count());

        $meta = [
            "published_at" => now()->addDays(2)
        ];
        Post::create("Future Post", "Some post in the past", $meta);
        $this->assertEquals(2, Post::scheduled()->count());
    }

    /**
     * @test
     */
    public function it_can_get_posts_by_author()
    {
        $meta = [
            'author' => $this->author
        ];
        $post = Post::create("Another Post", "Not really sure of what to write here! Can I get some help please?", $meta);
        $this->assertEquals($this->author->name, $post->author->name);

        $meta = [
            'author' => $this->author
        ];
        $post = Post::create("Another Post from same author", "Not really sure of what to write here! Can I get some help please?", $meta);
        $this->assertEquals($this->author->name, $post->author->name);

        $meta = [
            'author' => TestAuthorModel::create(['name' => 'Second Author'])
        ];
        $post = Post::create("Another Post", "Not really sure of what to write here! Can I get some help please?", $meta);
        $this->assertEquals($meta['author']->name, $post->author->name);

        $postsFromFirstAuthor = Post::byAuthor($this->author);
        $this->assertEquals(2, $postsFromFirstAuthor->count());

        $postsFromSecondAuthor = Post::byAuthor($meta['author']);
        $this->assertEquals(1, $postsFromSecondAuthor->count());

        $meta = [
            'author' => $this->author
        ];
        $post = Post::create("Another Post from the first author", "Not really sure of what to write here! Can I get some help please?", $meta);
        $this->assertEquals($this->author->name, $post->author->name);

        $postsFromFirstAuthor = Post::byAuthor($this->author);
        $this->assertEquals(3, $postsFromFirstAuthor->count());

    }
}
