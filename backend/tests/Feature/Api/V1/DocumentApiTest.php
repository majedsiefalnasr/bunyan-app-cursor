<?php

namespace Tests\Feature\Api\V1;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_project_documents(): void
    {
        $project = Project::factory()->create();

        $this->getJson("/api/v1/projects/{$project->id}/documents")
            ->assertStatus(401);
    }

    public function test_stranger_cannot_list_project_documents(): void
    {
        Storage::fake('public');
        $customer = User::factory()->customer()->create();
        $stranger = User::factory()->contractor()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $this->actingAs($stranger)->getJson("/api/v1/projects/{$project->id}/documents")
            ->assertStatus(403);
    }

    public function test_customer_can_upload_and_list_documents(): void
    {
        Storage::fake('public');
        $customer = User::factory()->customer()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);
        $file = UploadedFile::fake()->image('contract.jpg', 40, 40);

        $upload = $this->actingAs($customer)->post("/api/v1/projects/{$project->id}/documents", [
            'file' => $file,
            'title' => 'عقد أعمال',
            'category' => 'photo',
        ]);

        $upload->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'عقد أعمال')
            ->assertJsonPath('data.category', 'photo');

        $docId = (int) $upload->json('data.id');

        $list = $this->actingAs($customer)->getJson("/api/v1/projects/{$project->id}/documents");
        $list->assertOk()->assertJsonPath('success', true);
        $ids = collect($list->json('data'))->pluck('id')->all();
        $this->assertContains($docId, $ids);
    }

    public function test_customer_can_download_document(): void
    {
        Storage::fake('public');
        $customer = User::factory()->customer()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);
        $file = UploadedFile::fake()->image('note.jpg', 30, 30);

        $upload = $this->actingAs($customer)->post("/api/v1/projects/{$project->id}/documents", [
            'file' => $file,
            'title' => 'ملاحظة',
            'category' => 'photo',
        ]);

        $docId = (int) $upload->json('data.id');

        $this->actingAs($customer)->get("/api/v1/documents/{$docId}/download")
            ->assertOk();
    }

    public function test_customer_can_add_version_and_list_versions(): void
    {
        Storage::fake('public');
        $customer = User::factory()->customer()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);
        $first = UploadedFile::fake()->image('v1.jpg', 20, 20);

        $upload = $this->actingAs($customer)->post("/api/v1/projects/{$project->id}/documents", [
            'file' => $first,
            'title' => 'نسخة',
            'category' => 'blueprint',
        ]);

        $docId = (int) $upload->json('data.id');

        $second = UploadedFile::fake()->image('v2.jpg', 25, 25);
        $this->actingAs($customer)->post("/api/v1/projects/{$project->id}/documents", [
            'file' => $second,
            'title' => 'نسخة',
            'category' => 'blueprint',
            'document_id' => $docId,
        ])->assertCreated()->assertJsonPath('data.version', 2);

        $versions = $this->actingAs($customer)->getJson("/api/v1/documents/{$docId}/versions");
        $versions->assertOk()->assertJsonPath('success', true);
        $vers = collect($versions->json('data'))->pluck('version')->all();
        $this->assertContains(1, $vers);
        $this->assertContains(2, $vers);
    }

    public function test_contractor_cannot_delete_customer_upload_without_permission(): void
    {
        Storage::fake('public');
        $customer = User::factory()->customer()->create();
        $contractor = User::factory()->contractor()->create();
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
        ]);

        $file = UploadedFile::fake()->image('only-customer.jpg', 20, 20);
        $upload = $this->actingAs($customer)->post("/api/v1/projects/{$project->id}/documents", [
            'file' => $file,
            'title' => 'عقد',
            'category' => 'contract',
        ]);
        $docId = (int) $upload->json('data.id');

        $this->actingAs($contractor)->deleteJson("/api/v1/documents/{$docId}")
            ->assertStatus(403);
    }

    public function test_customer_can_delete_uploaded_contractor_document(): void
    {
        Storage::fake('public');
        $customer = User::factory()->customer()->create();
        $contractor = User::factory()->contractor()->create();
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
        ]);

        $file = UploadedFile::fake()->image('from-contractor.jpg', 20, 20);
        $upload = $this->actingAs($contractor)->post("/api/v1/projects/{$project->id}/documents", [
            'file' => $file,
            'title' => 'تسليم',
            'category' => 'report',
        ]);
        $docId = (int) $upload->json('data.id');

        $this->actingAs($customer)->deleteJson("/api/v1/documents/{$docId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('documents', ['id' => $docId]);
    }

    public function test_uploader_can_delete_own_document(): void
    {
        Storage::fake('public');
        $customer = User::factory()->customer()->create();
        $contractor = User::factory()->contractor()->create();
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
        ]);

        $file = UploadedFile::fake()->image('mine.jpg', 20, 20);
        $upload = $this->actingAs($contractor)->post("/api/v1/projects/{$project->id}/documents", [
            'file' => $file,
            'title' => 'خاص',
            'category' => 'other',
        ]);
        $docId = (int) $upload->json('data.id');

        $this->actingAs($contractor)->deleteJson("/api/v1/documents/{$docId}")
            ->assertOk();

        $this->assertSoftDeleted('documents', ['id' => $docId]);
    }
}
