<?php

namespace Tests\Feature;

use App\Jobs\ProcessContactSubmission;
use App\Models\Contact;
use App\Models\User;
use App\Services\Contacts\ContactService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_submit_contact(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/contacts', [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'message' => 'Gostaria de mais informações sobre o portal.',
        ]);

        $response->assertStatus(202)
            ->assertJsonPath('status', 'queued');

        Queue::assertPushed(ProcessContactSubmission::class, function (ProcessContactSubmission $job) {
            return $job->payload['name'] === 'Maria Silva'
                && $job->payload['email'] === 'maria@example.com';
        });

        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_process_contact_submission_job_persists_contact(): void
    {
        $job = new ProcessContactSubmission([
            'name' => 'João Job',
            'email' => 'joao-job@example.com',
            'message' => 'Mensagem persistida após o processamento assíncrono.',
        ]);

        $job->handle(app(ContactService::class));

        $this->assertDatabaseHas('contacts', [
            'name' => 'João Job',
            'email' => 'joao-job@example.com',
        ]);
    }

    public function test_contact_creation_validates_required_fields(): void
    {
        $response = $this->postJson('/api/contacts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'message']);
    }

    public function test_contact_message_must_meet_min_length(): void
    {
        $response = $this->postJson('/api/contacts', [
            'name' => 'João',
            'email' => 'joao@example.com',
            'message' => 'curta',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_admin_can_list_contacts_with_search(): void
    {
        $admin = User::factory()->create();

        Contact::factory()->create([
            'name' => 'Ana Contato',
            'email' => 'ana@example.com',
            'message' => 'Mensagem de teste com mais de dez caracteres.',
        ]);
        Contact::factory()->create([
            'name' => 'Outro',
            'email' => 'outro@example.com',
            'message' => 'Outra mensagem longa o suficiente aqui.',
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/api/admin/contacts?search=Ana&per_page=10');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Ana Contato');
    }

    public function test_unauthenticated_user_cannot_list_contacts(): void
    {
        $response = $this->getJson('/api/admin/contacts');

        $response->assertUnauthorized();
    }
}
