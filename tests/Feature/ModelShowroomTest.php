<?php

namespace Tests\Feature;

use App\Models\ModelShowroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModelShowroomTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'model_name' => 'Q50',
            'year' => 2014,
            'price' => 37500,
            'status' => 1,
            'display_order' => 1,
            'image' => UploadedFile::fake()->image('q50.png', 225, 110),
        ], $overrides);
    }

    // ---------------------------------------------------------------- homepage

    public function test_homepage_lists_only_active_models_in_display_order(): void
    {
        ModelShowroom::factory()->create(['model_name' => 'Second', 'display_order' => 2]);
        ModelShowroom::factory()->create(['model_name' => 'First', 'display_order' => 1]);
        ModelShowroom::factory()->inactive()->create(['model_name' => 'Hidden']);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['2014 First', '2014 Second'])
            ->assertDontSee('Hidden');
    }

    public function test_homepage_formats_price_like_the_design(): void
    {
        ModelShowroom::factory()->create(['model_name' => 'QX70', 'price' => 53350]);

        $this->get('/')->assertSee('Starting at $53,350');
    }

    // ------------------------------------------------------------------- auth

    public function test_guests_are_redirected_to_admin_login(): void
    {
        $this->get('/admin/models')->assertRedirect(route('admin.login'));
        $this->post('/admin/models', [])->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass']);

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'secret-pass'])
            ->assertRedirect(route('admin.models.index'));
        $this->assertAuthenticatedAs($user);

        $this->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->from('/admin/login')
            ->post('/admin/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ------------------------------------------------------------------- CRUD

    public function test_admin_pages_render(): void
    {
        $model = ModelShowroom::factory()->create(['model_name' => 'Q50']);

        $this->actingAs(User::factory()->create())->get('/admin/models')
            ->assertOk()->assertSee('Q50')->assertSee('Add Model');
        $this->get('/admin/models/create')->assertOk()->assertSee('Save Model');
        $this->get("/admin/models/{$model->id}/edit")->assertOk()->assertSee('Update Model');
    }

    public function test_admin_can_create_a_model_with_image(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/admin/models', $this->validPayload())
            ->assertRedirect(route('admin.models.index'));

        $model = ModelShowroom::firstOrFail();
        $this->assertSame('Q50', $model->model_name);
        $this->assertTrue($model->status);
        Storage::disk('public')->assertExists($model->image);
    }

    public function test_create_requires_valid_data_and_an_image(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/admin/models', ['model_name' => '', 'year' => 'abc', 'price' => -5])
            ->assertSessionHasErrors(['model_name', 'year', 'price', 'image']);

        $this->assertDatabaseCount('model_showrooms', 0);
    }

    public function test_image_must_be_an_image_file(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/admin/models', $this->validPayload(['image' => UploadedFile::fake()->create('virus.pdf', 10)]))
            ->assertSessionHasErrors('image');
    }

    public function test_admin_can_update_without_replacing_the_image(): void
    {
        $model = ModelShowroom::factory()->create(['image' => 'models/old.png']);
        Storage::disk('public')->put('models/old.png', 'x');

        $this->actingAs(User::factory()->create())
            ->put("/admin/models/{$model->id}", [
                'model_name' => 'Renamed', 'year' => 2015, 'price' => 40000,
                'display_order' => 9,   // status omitted/0 => inactive
                'status' => 0,
            ])
            ->assertRedirect(route('admin.models.index'));

        $model->refresh();
        $this->assertSame('Renamed', $model->model_name);
        $this->assertFalse($model->status);
        $this->assertSame('models/old.png', $model->image);
    }

    public function test_replacing_the_image_removes_the_old_file(): void
    {
        $model = ModelShowroom::factory()->create(['image' => 'models/old.png']);
        Storage::disk('public')->put('models/old.png', 'x');

        $this->actingAs(User::factory()->create())
            ->put("/admin/models/{$model->id}", [
                'model_name' => $model->model_name, 'year' => 2014, 'price' => 1000,
                'image' => UploadedFile::fake()->image('new.png'),
            ]);

        Storage::disk('public')->assertMissing('models/old.png');
        Storage::disk('public')->assertExists($model->refresh()->image);
    }

    public function test_admin_can_delete_a_model_and_its_image(): void
    {
        $model = ModelShowroom::factory()->create(['image' => 'models/a.png']);
        Storage::disk('public')->put('models/a.png', 'x');

        $this->actingAs(User::factory()->create())
            ->delete("/admin/models/{$model->id}")
            ->assertRedirect(route('admin.models.index'));

        $this->assertModelMissing($model);
        Storage::disk('public')->assertMissing('models/a.png');
    }

    // -------------------------------------------------------------------- API

    public function test_api_index_is_public_and_only_returns_active_models(): void
    {
        ModelShowroom::factory()->create(['model_name' => 'Visible']);
        ModelShowroom::factory()->inactive()->create(['model_name' => 'Hidden']);

        $this->getJson('/api/model-showrooms')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.model_name', 'Visible');
    }

    public function test_api_write_endpoints_require_a_token(): void
    {
        $this->postJson('/api/model-showrooms', [])->assertUnauthorized();
        $this->putJson('/api/model-showrooms/1', [])->assertUnauthorized();
        $this->deleteJson('/api/model-showrooms/1')->assertUnauthorized();
    }

    public function test_api_login_returns_a_working_token(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass']);

        $token = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'secret-pass'])
            ->assertOk()
            ->json('token');

        $this->withToken($token)->postJson('/api/model-showrooms', [])->assertUnprocessable();
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'nope'])->assertUnauthorized();
    }

    public function test_api_full_crud_cycle(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum');

        $id = $this->post('/api/model-showrooms', $this->validPayload(), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.model_name', 'Q50')
            ->json('data.id');

        $this->putJson("/api/model-showrooms/{$id}", ['price' => 39999, 'status' => false])
            ->assertOk()
            ->assertJsonPath('data.price', 39999)
            ->assertJsonPath('data.status', false)
            ->assertJsonPath('data.model_name', 'Q50');     // untouched fields stay

        $this->deleteJson("/api/model-showrooms/{$id}")->assertOk();
        $this->assertDatabaseMissing('model_showrooms', ['id' => $id]);
        $this->deleteJson("/api/model-showrooms/{$id}")->assertNotFound();
    }
}
