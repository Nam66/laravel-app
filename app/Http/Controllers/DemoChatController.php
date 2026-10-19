<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoChatController extends Controller
{
    public function index(): JsonResponse
    {
        $user = $this->demoUser();

        $conversations = Conversation::query()
            ->whereHas('participants', fn (Builder $query) => $query
                ->where('user_id', $user->id)
                ->whereNull('left_at'))
            ->with([
                'participants' => fn ($query) => $query
                    ->whereNull('left_at')
                    ->with('user:id,name'),
                'latestMessage.sender:id,name',
            ])
            ->withCount([
                'participants as participants_count' => fn (Builder $query) => $query->whereNull('left_at'),
                'messages',
            ])
            ->orderByDesc('updated_at')
            ->get();

        $unreadCounts = Message::query()
            ->select('messages.conversation_id', DB::raw('COUNT(*) AS aggregate'))
            ->join('conversation_participants', 'conversation_participants.conversation_id', '=', 'messages.conversation_id')
            ->where('conversation_participants.user_id', $user->id)
            ->whereNull('conversation_participants.left_at')
            ->where(function (Builder $query) {
                $query->whereNull('conversation_participants.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'conversation_participants.last_read_at');
            })
            ->where(function (Builder $query) use ($user) {
                $query->whereNull('messages.sender_id')
                    ->orWhere('messages.sender_id', '!=', $user->id);
            })
            ->groupBy('messages.conversation_id')
            ->pluck('aggregate', 'messages.conversation_id');

        $notifications = $user->notifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Notification',
                'body' => $notification->data['body'] ?? '',
                'conversation' => $notification->data['conversation'] ?? null,
                'created_at' => $notification->created_at?->toISOString(),
                'read_at' => $notification->read_at?->toISOString(),
            ])
            ->values();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'workspace_members_count' => User::query()
                ->whereHas('conversationParticipants', fn (Builder $query) => $query->whereNull('left_at'))
                ->distinct()
                ->count(),
            'conversations' => $conversations->map(fn (Conversation $conversation) => [
                'id' => $conversation->id,
                'name' => $conversation->name ?? $this->otherParticipantName($conversation, $user),
                'type' => $conversation->type,
                'participants_count' => $conversation->participants_count,
                'messages_count' => $conversation->messages_count,
                'unread_count' => (int) ($unreadCounts[$conversation->id] ?? 0),
                'updated_at' => $conversation->updated_at?->toISOString(),
                'latest_message' => $this->messageData($conversation->latestMessage),
            ])->values(),
            'notifications' => $notifications,
            'unread_notifications_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function messages(Conversation $conversation): JsonResponse
    {
        $user = $this->demoUser();
        $this->activeMembership($conversation, $user);

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (Message $message) => $this->messageData($message, $user));

        return response()->json(['data' => $messages]);
    }

    public function storeMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $this->demoUser();
        $membership = $this->activeMembership($conversation, $user);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = DB::transaction(function () use ($conversation, $membership, $user, $validated): Message {
            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'client_message_id' => (string) Str::uuid(),
                'type' => 'text',
                'body' => trim($validated['body']),
            ]);

            $conversation->touch();
            $membership->update(['last_read_at' => $message->created_at]);

            return $message->load('sender:id,name');
        });

        return response()->json(['data' => $this->messageData($message, $user)], 201);
    }

    public function markRead(Conversation $conversation): JsonResponse
    {
        $user = $this->demoUser();
        $membership = $this->activeMembership($conversation, $user);
        $membership->update(['last_read_at' => now()]);

        return response()->json(['read_at' => $membership->last_read_at?->toISOString()]);
    }

    private function demoUser(): User
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        return User::query()
            ->where('email', 'alice@example.test')
            ->firstOrFail();
    }

    private function activeMembership(Conversation $conversation, User $user): ConversationParticipant
    {
        return $conversation->participants()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->firstOrFail();
    }

    private function otherParticipantName(Conversation $conversation, User $user): ?string
    {
        return $conversation->participants
            ->first(fn (ConversationParticipant $participant) => $participant->user_id !== $user->id)
            ?->user
            ?->name;
    }

    /**
     * @return array<string, int|string|null>|null
     */
    private function messageData(?Message $message, ?User $viewer = null): ?array
    {
        if ($message === null) {
            return null;
        }

        return [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'sender' => $message->sender?->name ?? 'Deleted user',
            'body' => $message->body,
            'type' => $message->type,
            'created_at' => $message->created_at?->toISOString(),
            'is_own' => $viewer !== null && $message->sender_id === $viewer->id,
        ];
    }
}
