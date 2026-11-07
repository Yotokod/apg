<?php

namespace Tests\Feature;

use App\Filament\Resources\GalleryItems\Pages\ManageGalleryItems;
use App\Models\GalleryItem;
use App\Models\User;
use App\Support\ImageThumbs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class GalleryAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function item(array $attrs = []): GalleryItem
    {
        return GalleryItem::create($attrs + [
            'image' => 'gallery/x.jpg', 'title' => ['fr' => 'Photo'], 'alt' => ['fr' => 'Alt'],
            'category' => 'poussins', 'position' => 0, 'is_active' => true, 'is_featured' => false,
        ]);
    }

    private function page()
    {
        return Livewire::actingAs($this->admin)->test(ManageGalleryItems::class);
    }

    public function test_permissions(): void
    {
        $this->get('/admin/gallery-items')->assertRedirect();
        $this->actingAs(User::factory()->create(['role' => 'customer']))->get('/admin/gallery-items')->assertForbidden();
        $this->actingAs($this->admin)->get('/admin/gallery-items')->assertOk()->assertSee('Ajouter plusieurs photos');
    }

    public function test_bulk_upload_creates_items_and_thumbnails(): void
    {
        $this->item(['position' => 5]);

        $this->page()->callAction('bulkUpload', data: [
            'images' => [UploadedFile::fake()->image('a.jpg', 1600, 900), UploadedFile::fake()->image('b.png', 400, 300)],
            'category' => 'oeufs', 'title_fr' => 'Ferme APG', 'title_en' => 'APG farm', 'is_featured' => true, 'is_active' => true,
        ])->assertHasNoActionErrors();

        $items = GalleryItem::where('category', 'oeufs')->orderBy('position')->get();
        $this->assertCount(2, $items);
        $this->assertSame(['fr' => 'Ferme APG 1', 'en' => 'APG farm 1'], $items[0]->title);
        $this->assertSame([6, 7], $items->pluck('position')->all());
        $this->assertTrue($items->every(fn ($i) => $i->is_featured && $i->is_active && str_starts_with($i->image, 'gallery/')));

        foreach ($items as $item) {
            Storage::disk('public')->assertExists($item->image);
            Storage::disk('public')->assertExists(ImageThumbs::pathFor($item->image));
        }
        [$width] = getimagesize(Storage::disk('public')->path(ImageThumbs::pathFor($items[0]->image)));
        $this->assertSame(ImageThumbs::MAX_WIDTH, $width);
    }

    public function test_bulk_upload_new_category_and_validation(): void
    {
        $this->page()->callAction('bulkUpload', data: ['images' => [], 'category' => 'autre'])->assertHasActionErrors(['images' => 'required']);

        $this->page()->callAction('bulkUpload', data: [
            'images' => [UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')], 'category' => 'autre',
        ])->assertHasActionErrors(['images']);

        $this->page()->mountAction('bulkUpload')
            ->fillForm(['images' => [UploadedFile::fake()->image('c.jpg')], 'new_category' => 'Basse cour'])
            ->assertSchemaStateSet(['category' => 'basse-cour'], 'mountedActionSchema0')
            ->callMountedAction()->assertHasNoActionErrors();
        $this->assertSame(1, GalleryItem::where('category', 'basse-cour')->count());
        $this->assertArrayHasKey('basse-cour', GalleryItem::categoryOptions());
    }

    public function test_editing_image_regenerates_thumbnail_and_cleans_old_upload(): void
    {
        Storage::disk('public')->put('gallery/old.jpg', UploadedFile::fake()->image('old.jpg', 300, 200)->getContent());
        $item = $this->item(['image' => 'gallery/old.jpg']);
        Storage::disk('public')->assertExists('thumbs/gallery/old.jpg.webp');

        Storage::disk('public')->put('gallery/new.jpg', UploadedFile::fake()->image('new.jpg', 300, 200)->getContent());
        $item->update(['image' => 'gallery/new.jpg']);

        Storage::disk('public')->assertExists('thumbs/gallery/new.jpg.webp');
        Storage::disk('public')->assertMissing('gallery/old.jpg');
        Storage::disk('public')->assertMissing('thumbs/gallery/old.jpg.webp');
    }

    public function test_delete_removes_only_gallery_uploads(): void
    {
        $disk = Storage::disk('public');
        foreach (['gallery/mine.jpg', 'wp-media/2024/wp.jpg'] as $path) {
            $disk->put($path, UploadedFile::fake()->image('i.jpg')->getContent());
        }
        $mine = $this->item(['image' => 'gallery/mine.jpg']);
        $wp = $this->item(['image' => 'wp-media/2024/wp.jpg']);
        $disk->assertExists('thumbs/wp-media/2024/wp.jpg.webp');

        $this->page()->callTableBulkAction('delete', [$mine, $wp]);

        $this->assertSame(0, GalleryItem::count());
        $disk->assertMissing('gallery/mine.jpg');
        $disk->assertMissing('thumbs/gallery/mine.jpg.webp');
        $disk->assertExists('wp-media/2024/wp.jpg');
        $disk->assertExists('thumbs/wp-media/2024/wp.jpg.webp');
    }

    public function test_shared_file_is_kept_while_still_referenced(): void
    {
        Storage::disk('public')->put('gallery/shared.jpg', 'x');
        $a = $this->item(['image' => 'gallery/shared.jpg']);
        $this->item(['image' => 'gallery/shared.jpg']);
        $a->delete();
        Storage::disk('public')->assertExists('gallery/shared.jpg');
    }

    public function test_filters(): void
    {
        $chick = $this->item(['category' => 'poussins', 'is_featured' => true]);
        $egg = $this->item(['category' => 'oeufs', 'is_active' => false]);
        $noAlt = $this->item(['category' => 'oeufs', 'alt' => null]);
        $emptyAlt = $this->item(['category' => 'oeufs', 'alt' => ['fr' => '', 'en' => 'Alt']]);

        $this->page()->filterTable('category', 'oeufs')->assertCanSeeTableRecords([$egg, $noAlt])->assertCanNotSeeTableRecords([$chick]);
        $this->page()->filterTable('is_featured', true)->assertCanSeeTableRecords([$chick])->assertCanNotSeeTableRecords([$egg]);
        $this->page()->filterTable('is_active', false)->assertCanSeeTableRecords([$egg])->assertCanNotSeeTableRecords([$chick]);
        $this->page()->filterTable('missing_alt')->assertCanSeeTableRecords([$noAlt, $emptyAlt])->assertCanNotSeeTableRecords([$chick, $egg]);
    }

    public function test_bulk_actions_and_featured_first(): void
    {
        $a = $this->item(['position' => 0, 'is_active' => false]);
        $b = $this->item(['position' => 1, 'is_active' => false]);
        $c = $this->item(['position' => 2]);

        $this->page()->callTableBulkAction('activate', [$a, $b]);
        $this->assertTrue($a->fresh()->is_active && $b->fresh()->is_active);
        $this->page()->callTableBulkAction('deactivate', [$a]);
        $this->assertFalse($a->fresh()->is_active);
        $this->page()->callTableBulkAction('feature', [$c]);
        $this->assertTrue($c->fresh()->is_featured);
        $this->page()->callTableBulkAction('unfeature', [$c]);
        $this->assertFalse($c->fresh()->is_featured);
        $this->page()->callTableBulkAction('changeCategory', [$a, $b], data: ['category' => 'livraison']);
        $this->assertSame(['livraison', 'livraison', 'poussins'], [$a->fresh()->category, $b->fresh()->category, $c->fresh()->category]);

        $c->update(['is_featured' => true]);
        $this->page()->callAction('featuredFirst');
        $this->assertSame([1, 2, 0], [$a->fresh()->position, $b->fresh()->position, $c->fresh()->position]);
    }
}
