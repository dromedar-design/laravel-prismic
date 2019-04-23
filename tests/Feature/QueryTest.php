<?php

namespace DromedarDesign\Prismic\Tests\Feature;

use DromedarDesign\Prismic\Tests\Post;
use DromedarDesign\Prismic\Tests\TestCase;
use Prismic\Api;
use Prismic\Predicates;

class QueryTest extends TestCase
{
    public $api;
    public $type;

    public function setUp(): void
    {
        parent::setUp();

        $this->api = resolve(Api::class);
        $this->type = (new Post)->getTable();
    }

    public function compare($api, $eloquent)
    {
        $this->assertEquals($api->id, $eloquent->id);
        $this->assertEquals($api->uid, $eloquent->uid);
        $this->assertEquals($api->href, $eloquent->href);
        $this->assertEquals($api->first_publication_date, $eloquent->first_publication_date);
        $this->assertEquals($api->lang, $eloquent->lang);
    }

    public function compareAll($api, $eloquent)
    {
        $api = collect($api)->values();
        $eloquent = $eloquent->values();

        $this->assertEquals(count($api), count($eloquent));
        $api->each(function ($item, $key) use ($eloquent) {
            $this->compare($item, $eloquent->get($key));
        });
    }

    /** @test */
    public function get_the_last_item()
    {
        $api = $this->api->getSingle($this->type);
        $eloquent = Post::first();

        $this->compare($api, $eloquent);
    }

    /** @test */
    public function query_by_uid()
    {
        $slug = 'second-post';
        $api = $this->api->getByUID($this->type, $slug);
        $eloquent = Post::find($slug);

        $this->compare($api, $eloquent);
    }

    /** @test */
    public function query_multiple_by_uid()
    {
        $uids = ['first-post', 'second-post'];
        $api = collect($this->api->query(
            Predicates::at('document.type', $this->type))
                ->results)
                ->filter(function ($item) use ($uids) {
                return in_array($item->uid, $uids);
            });
        $eloquent = Post::find($uids);

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function query_by_id()
    {
        $id = 'XLnSDyoAAD0AKo0Z';
        $api = $this->api->getByID($id);
        $eloquent = Post::where('id', $id)->first();

        $this->compare($api, $eloquent);
    }

    /** @test */
    public function query_multiple_by_id()
    {
        $ids = ['XLnSDyoAAD0AKo0Z', 'XLnTtioAAD0AKpRw'];
        $api = $this->api->getByIDs($ids)->results;
        $eloquent = Post::whereIn('id', $ids)->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function query_by_id_changing_the_primary_key()
    {
        $id = 'XLnSDyoAAD0AKo0Z';
        $api = $this->api->getByID($id);

        $post = new Post;
        $post->setKeyName('id');
        $eloquent = $post->find($id);

        $this->compare($api, $eloquent);
    }

    /** @test */
    public function query_last_by_language()
    {
        $api = $this->api->getSingle($this->type, ['lang' => 'hu']);
        $eloquent = Post::whereLanguage('hu')->first();

        $this->compare($api, $eloquent);
    }

    /** @test */
    public function query_multiple_by_language()
    {
        $uids = ['first-post', 'second-post'];
        $api = collect($this->api->query(
            Predicates::at('document.type', $this->type),
            ['lang' => 'hu'])->results)
            ->filter(function ($item) use ($uids) {
                return in_array($item->uid, $uids);
            });
        $eloquent = Post::whereLanguage('hu')->find($uids);

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function query_all_by_language()
    {
        $api = $this->api->query(
            Predicates::at('document.type', $this->type),
            ['lang' => 'hu'])->results;
        $eloquent = Post::whereLanguage('hu')->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function pagination()
    {
        $api = $this->api->query(
            Predicates::at('document.type', $this->type),
            ['pageSize' => 1])->results;
        $eloquent = Post::paginate(1);

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function pagination_on_second_page()
    {
        $api = $this->api->query(
            Predicates::at('document.type', $this->type),
            ['pageSize' => 1, 'page' => 2])->results;
        $eloquent = Post::paginate(1, ['*'], 'page', 2);

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function ordering_asc()
    {
        $attr = 'title';
        $order = 'asc';

        $api = $this->api->query(
            Predicates::at('document.type', $this->type),
            ['orderings' => "[my.{$this->type}.{$attr}]"]
        )->results;
        $eloquent = Post::orderBy($attr)->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function ordering_desc()
    {
        $attr = 'title';
        $order = 'desc';

        $api = $this->api->query(
            Predicates::at('document.type', $this->type),
            ['orderings' => "[my.{$this->type}.{$attr} {$order}]"]
        )->results;
        $eloquent = Post::orderBy($attr, $order)->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function query_by_one_tag()
    {
        $tags = ['another_tag'];

        $api = $this->api->query([
            Predicates::at('document.type', $this->type),
            Predicates::at('document.tags', $tags),
        ])->results;
        $eloquent = Post::whereTags($tags)->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function query_by_multiple_tags()
    {
        $tags = ['another_tag', 'multiple_languages'];

        $api = $this->api->query([
            Predicates::at('document.type', $this->type),
            Predicates::any('document.tags', $tags),
        ])->results;
        $eloquent = Post::whereTags($tags)->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function query_by_tags_and_multiple_languages()
    {
        $tags = ['another_tag', 'multiple_languages'];

        $api = $this->api->query([
            Predicates::at('document.type', $this->type),
            Predicates::any('document.tags', $tags),
        ], ['lang' => '*'])->results;
        $eloquent = Post::whereTags($tags)->whereLanguage('*')->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function not_equals()
    {
        $id = 'XLnSDyoAAD0AKo0Z';

        $api = $this->api->query([
            Predicates::at('document.type', $this->type),
            Predicates::not('document.id', $id),
        ])->results;
        $eloquent = Post::where('id', '!=', $id)->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function search_for_anything()
    {
        $search = 'second';

        $api = $this->api->query(Predicates::fulltext('document', $search))->results;
        $eloquent = Post::search($search)->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function search_in_field()
    {
        $search = 'second';
        $field = 'title';

        $api = $this->api->query(Predicates::fulltext("my.{$this->type}.{$field}", $search))->results;
        $eloquent = Post::search($search, $field)->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function less_than()
    {
        $number = 40;
        $field = 'price';

        $api = $this->api->query(Predicates::lt("my.{$this->type}.{$field}", $number))->results;
        $eloquent = Post::where($field, '<', $number)->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function greater_than()
    {
        $number = 40;
        $field = 'price';

        $api = $this->api->query(Predicates::gt("my.{$this->type}.{$field}", $number))->results;
        $eloquent = Post::where($field, '>', $number)->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function less_than_or_equals()
    {
        $number = 30;
        $field = 'price';

        $api = $this->api->query([
            Predicates::lt("my.{$this->type}.{$field}", $number),
            Predicates::at("my.{$this->type}.{$field}", $number),
        ])->results;
        $eloquent = Post::where($field, '<=', $number)->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function greater_than_or_equals()
    {
        $number = 30;
        $field = 'price';

        $api = $this->api->query([
            Predicates::gt("my.{$this->type}.{$field}", $number),
            Predicates::at("my.{$this->type}.{$field}", $number),
        ])->results;
        $eloquent = Post::where($field, '>=', $number)->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function find_relationship()
    {
        $field = 'category_uid';
        $rel = 'category';

        $category = $this->api->getSingle($rel);
        $api = $this->api->query(
            [Predicates::at('document.type', $this->type),
                Predicates::at("my.{$this->type}.{$field}", $category->id)]
        )->results;
        $eloquent = Post::where($field, $category->id)->get();

        $this->compareAll($api, $eloquent);
    }

    /** @test */
    public function query_relationship()
    {
        $slug = 'second-post';
        $rel = 'category';

        $cat = $this->api->getByUID($this->type, $slug)->data->category_uid;
        $api = $this->api->getByUID($rel, $cat->uid);
        $eloquent = Post::find($slug)->{$rel};

        $this->compare($api, $eloquent);
    }
}
