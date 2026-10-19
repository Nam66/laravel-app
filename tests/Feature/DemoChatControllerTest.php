<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoChatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_conversations_and_messages_from_the_database(): void
    {
        [$alice, $maya, $conversation] = $this->createDemoConversation();
        $message = $conversation->messages()->create([
            'sender_id' => $maya->id,
            'client_message_id' => '5b1e02c1-a492-4f62-a58b-9b8a9f57f70c',
            'type' => 'text',
            'body' => 'Read this from the database.',
        ]);

        $this->getJson('/api/demo/chat')
            ->assertOk()
            ->assertJsonPath('user.id', $alice->id)
            ->assertJsonPath('conversations.0.id', $conversation->id)
            ->assertJsonPath('conversations.0.latest_message.id', $message->id)
            ->assertJsonPath('conversations.0.latest_message.body', 'Read this from the database.')
            ->assertJsonPath('conversations.0.unread_count', 1);

        $this->getJson("/api/demo/conversations/{$conversation->id}/messages")
            ->assertOk()
            ->assertJsonPath('data.0.id', $message->id)
            ->assertJsonPath('data.0.sender', 'Maya Chen')
            ->assertJsonPath('data.0.is_own', false);
    }

    public function test_it_persists_a_message_sent_by_the_demo_user(): void
    {
        [$alice, , $conversation] = $this->createDemoConversation();

        $response = $this->postJson("/api/demo/conversations/{$conversation->id}/messages", [
            'body' => 'Persist this message.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.sender_id', $alice->id)
            ->assertJsonPath('data.body', 'Persist this message.')
            ->assertJsonPath('data.is_own', true);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $alice->id,
            'body' => 'Persist this message.',
        ]);
    }

    public function test_it_does_not_allow_the_demo_user_to_access_a_conversation_they_have_left(): void
    {
        [$alice, , $conversation] = $this->createDemoConversation();
        $conversation->participants()->where('user_id', $alice->id)->update(['left_at' => now()]);

        $this->getJson("/api/demo/conversations/{$conversation->id}/messages")
            ->assertNotFound();
    }

    /**
     * @return array{0: User, 1: User, 2: Conversation}
     */
    private function createDemoConversation(): array
    {
        $alice = User::create([
            'name' => 'Alice Morgan',
            'email' => 'alice@example.test',
            'password' => Hash::make('password'),
        ]);

        $maya = User::create([
            'name' => 'Maya Chen',
            'email' => 'maya@example.test',
            'password' => Hash::make('password'),
        ]);

        $conversation = Conversation::create([
            'type' => 'direct',
            'name' => 'Maya Chen',
            'created_by' => $alice->id,
        ]);

        $conversation->participants()->createMany([
            ['user_id' => $alice->id, 'role' => 'owner', 'last_read_at' => now()->subDay()],
            ['user_id' => $maya->id, 'role' => 'member', 'last_read_at' => now()],
        ]);

        return [$alice, $maya, $conversation];
    }
}
