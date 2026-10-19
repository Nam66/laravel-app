<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ChatDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $alice = $this->user('Alice Morgan', 'alice@example.test');
            $maya = $this->user('Maya Chen', 'maya@example.test');
            $noah = $this->user('Noah Williams', 'noah@example.test');
            $sophie = $this->user('Sophie Laurent', 'sophie@example.test');

            $this->conversation('direct', 'Maya Chen', $alice, [$alice, $maya], [
                ['maya@example.test', 'Hey Alice! I just shared the updated project brief.', 49],
                ['alice@example.test', 'Perfect, I will take a look before our sync.', 44],
                ['maya@example.test', 'The new onboarding flow is looking much cleaner now.', 35],
                ['alice@example.test', 'Agreed. The step-by-step progress makes a big difference.', 28],
                ['maya@example.test', 'I added the empty states too. Would love your thoughts ✨', 12],
            ], $maya->id);

            $this->conversation('direct', 'Noah Williams', $alice, [$alice, $noah], [
                ['alice@example.test', 'Are we still on for the design review this afternoon?', 180],
                ['noah@example.test', 'Absolutely. I will bring the latest component library.', 172],
                ['alice@example.test', 'Great, I have a few ideas for the message composer.', 165],
                ['noah@example.test', 'Looking forward to it! The new direction feels really solid.', 151],
            ], $alice->id);

            $this->conversation('group', 'Product team', $alice, [$alice, $maya, $noah, $sophie], [
                ['sophie@example.test', 'Sprint planning notes are ready in the shared space.', 360],
                ['alice@example.test', 'Thanks Sophie! I added the chat milestones.', 345],
                ['noah@example.test', 'Realtime delivery is on track for the next iteration.', 322],
                ['maya@example.test', 'I can take the notification preferences screen.', 305],
                ['alice@example.test', 'Amazing. Let us keep the first release focused and fast.', 286],
            ], $alice->id);

            $this->conversation('direct', 'Sophie Laurent', $alice, [$alice, $sophie], [
                ['sophie@example.test', 'I found a few great references for the activity feed.', 1440],
                ['alice@example.test', 'Send them over when you get a chance, thank you!', 1417],
                ['sophie@example.test', 'Just sent the links. The second one has a nice timeline pattern.', 1390],
            ], $alice->id);

            $alice->notifications()->firstOrCreate(
                ['id' => 'd19f4e82-53b7-4f6e-89bf-16c61a001001'],
                [
                    'type' => 'App\\Notifications\\DemoMessageNotification',
                    'data' => [
                        'title' => 'New message from Maya Chen',
                        'body' => 'I added the empty states too. Would love your thoughts.',
                        'conversation' => 'Maya Chen',
                    ],
                    'read_at' => null,
                ],
            );
        });
    }

    private function user(string $name, string $email): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
    }

    /**
     * @param  array<int, User>  $users
     * @param  array<int, array{0: string, 1: string, 2: int}>  $messages
     */
    private function conversation(
        string $type,
        string $name,
        User $creator,
        array $users,
        array $messages,
        int $minutesReadAgo,
    ): void {
        $conversation = Conversation::firstOrCreate(
            ['type' => $type, 'name' => $name],
            ['created_by' => $creator->id],
        );

        foreach ($users as $user) {
            $conversation->participants()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'role' => $user->is($creator) ? 'owner' : 'member',
                    'last_read_at' => $user->is($creator) ? now()->subMinutes($minutesReadAgo) : now(),
                ],
            );
        }

        $lastMessageId = count($messages);

        foreach ($messages as $index => [$email, $body, $minutesAgo]) {
            $sender = collect($users)->firstWhere('email', $email);
            $sentAt = now()->subMinutes($minutesAgo);
            $clientMessageId = sprintf(
                'd19f4e82-53b7-4f6e-89bf-%012d',
                $conversation->id * 100 + $index + 1,
            );

            Message::firstOrCreate(
                [
                    'conversation_id' => $conversation->id,
                    'client_message_id' => $clientMessageId,
                ],
                [
                    'sender_id' => $sender->id,
                    'type' => 'text',
                    'body' => $body,
                    'created_at' => $sentAt,
                    'updated_at' => $sentAt,
                ],
            );
        }

        $conversation->updateQuietly([
            'updated_at' => now()->subMinutes($messages[$lastMessageId - 1][2]),
        ]);
    }
}
