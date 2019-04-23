<?php

namespace DromedarDesign\Prismic\Tests\Feature;

use DromedarDesign\Prismic\Field;
use DromedarDesign\Prismic\Item;
use DromedarDesign\Prismic\Slice;
use DromedarDesign\Prismic\Tests\Post;
use DromedarDesign\Prismic\Tests\TestCase;
use Prismic\Api;
use Prismic\Dom\RichText;

class TemplateTest extends TestCase
{
    public $api;
    public $type;
    public $response;

    public function setUp(): void
    {
        parent::setUp();

        $this->api = resolve(Api::class);

        $this->response = include __DIR__ . '/../response.php';
        $this->model = Post::make($this->response);
        $this->type = $this->model->getTable();
    }

    /** @test */
    public function saved_response_correctly_matches_real_reponse()
    {
        $model = Post::find('first-post');

        $this->assertEquals($this->response, $model->getAttributes());
    }

    /** @test */
    public function accessing_a_model_property_creates_a_field_object()
    {
        $field = 'title';

        $this->assertInstanceOf(Field::class, $this->model->{$field});
    }

    /** @test */
    public function accessing_a_nonexisting_model_property_returns_null()
    {
        $field = 'doesnt_exists';

        $this->assertEquals(null, $this->model->{$field});
    }

    /** @test */
    public function properties_inside_data_can_be_accesses_via_raw()
    {
        $field = 'title';

        $this->assertEquals($this->model->data->{$field}, $this->model->{$field}->raw);
    }

    /** @test */
    public function text_is_returned_when_accessing_via_text()
    {
        $field = 'title';

        $text = RichText::asText($this->model->data->{$field});
        $this->assertEquals($text, $this->model->{$field}->text);
    }

    /** @test */
    public function data_is_transformed_when_accessing_via_html()
    {
        $field = 'title';

        $html = RichText::asHtml($this->model->data->{$field});
        $this->assertEquals($html, $this->model->{$field}->html);
    }

    /** @test */
    public function data_is_transformed_when_casted_to_string()
    {
        $field = 'title';

        $html = RichText::asHtml($this->model->data->{$field});
        $this->assertEquals($html, (string) $this->model->{$field});
    }

    /** @test */
    public function change_between_modes()
    {
        $field = 'title';
        $html = RichText::asHtml($this->model->data->{$field});
        $this->assertEquals($html, (string) $this->model->{$field});

        $this->model->setPrismicMode('text');
        $text = RichText::asText($this->model->data->{$field});
        $this->assertEquals($text, (string) $this->model->{$field});
    }

    /** @test */
    public function accessing_body_key_returns_an_array_of_slices()
    {
        $body = $this->model->{$this->model->bodyKey};

        $this->assertInstanceOf(Slice::class, $body[0]);
    }

    /** @test */
    public function accessing_properties_inside_a_slice_creates_field_object()
    {
        $body = $this->model->{$this->model->bodyKey};

        $this->assertInstanceOf(Field::class, $body[0]->text);
    }

    /** @test */
    public function accessing_items_inside_a_slice_returns_an_array_of_items()
    {
        $body = $this->model->{$this->model->bodyKey};
        $items = $body[0]->items;

        $this->assertInstanceOf(Item::class, $items[0]);
    }

    /** @test */
    public function a_slice_always_has_a_type()
    {
        $body = $this->model->{$this->model->bodyKey};

        foreach ($body as $slice) {
            $this->assertIsString($slice->slice_type);
        }
    }
}
