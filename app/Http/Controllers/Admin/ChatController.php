<?php

namespace App\Http\Controllers\Admin;

use App\Events\AdminChatMessageSent;
use App\Events\AdminChatMessagesRead;
use App\Http\Controllers\Controller;
use App\Models\AdminChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeChat();

        $currentId = Auth::id();
        $users = User::where('status', 'active')
            ->orderBy('name')
            ->get();

        $selectedUserId = $request->input('with');
        $selectedUser = $users->where('id', $selectedUserId)->first() ?? $users->first();
        $messages = $selectedUser ? $this->conversationWith($selectedUser) : collect();
        if ($selectedUser) {
            $this->markConversationRead($selectedUser->id);
        }

        $unreadCounts = AdminChatMessage::query()
            ->selectRaw('sender_user_id, COUNT(*) as count')
            ->where('recipient_user_id', $currentId)
            ->whereNull('read_at')
            ->groupBy('sender_user_id')
            ->pluck('count', 'sender_user_id');

        if ($selectedUser) {
            $unreadCounts->forget($selectedUser->id);
        }

        $lastMessageIds = AdminChatMessage::query()
            ->selectRaw('MAX(id) as id, CASE WHEN sender_user_id = ? THEN recipient_user_id ELSE sender_user_id END as peer_id', [$currentId])
            ->where(function ($query) use ($currentId) {
                $query->where('sender_user_id', $currentId)
                    ->orWhere('recipient_user_id', $currentId);
            })
            ->groupBy('peer_id')
            ->pluck('id', 'peer_id');

        $lastMessages = AdminChatMessage::with('sender')
            ->whereIn('id', $lastMessageIds->values())
            ->get()
            ->keyBy('id');

        $chatMeta = $users->mapWithKeys(function (User $user) use ($unreadCounts, $lastMessageIds, $lastMessages, $currentId) {
            $lastId = $lastMessageIds->get($user->id);
            $last = $lastId ? $lastMessages->get($lastId) : null;
            $lastMessage = $last?->message ? Str::limit($last->message, 60, '...') : null;

            return [
                $user->id => [
                    'unread_count' => (int) ($unreadCounts->get($user->id) ?? 0),
                    'last_message' => $lastMessage,
                    'last_at' => $last?->created_at?->toIsoString(),
                    'last_is_sender' => $last ? $last->sender_user_id === $currentId : null,
                ],
            ];
        });

        return view('admin.chat', compact('users', 'messages', 'selectedUser', 'chatMeta'));
    }

    public function storeMessage(Request $request)
    {
        $this->authorizeChat();

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'recipient_user_id' => ['required', 'exists:users,id'],
        ]);

        $message = AdminChatMessage::create([
            'sender_user_id' => Auth::id(),
            'recipient_user_id' => $data['recipient_user_id'],
            'message' => trim($data['message']),
        ]);

        broadcast(new AdminChatMessageSent($message))->toOthers();

        return response()->json([
            'message' => $message->load('sender'),
        ], 201);
    }

    private function authorizeChat()
    {
        if (function_exists('userHasPermission')) {
            abort_unless(userHasPermission('cases.view'), 403);
        } else {
            abort_unless(auth()->user()?->hasPermission('cases.view'), 403);
        }
    }

    public function conversation(User $user)
    {
        $this->authorizeChat();

        $messages = $this->conversationWith($user);

        $this->markConversationRead($user->id);

        $serialized = $messages->map(function (AdminChatMessage $message) {
            return [
                'id' => $message->id,
                'message' => $message->message,
                'sender_name' => $message->sender?->name,
                'sender_id' => $message->sender_user_id,
                'recipient_id' => $message->recipient_user_id,
                'created_at' => $message->created_at?->toIsoString(),
                'read_at' => $message->read_at?->toIsoString(),
            ];
        })->values();

        return response()->json([
            'messages' => $serialized,
        ]);
    }

    public function markRead(Request $request)
    {
        $this->authorizeChat();

        $data = $request->validate([
            'sender_user_id' => ['required', 'exists:users,id'],
        ]);

        $readAt = now();
        $count = AdminChatMessage::query()
            ->where('sender_user_id', $data['sender_user_id'])
            ->where('recipient_user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => $readAt]);

        if ($count > 0) {
            broadcast(new AdminChatMessagesRead($data['sender_user_id'], Auth::id(), $readAt))->toOthers();
        }

        return response()->json(['updated' => $count, 'read_at' => $readAt]);
    }

    private function conversationWith(User $user): Collection
    {
        return AdminChatMessage::with('sender')
            ->where(function ($query) use ($user) {
                $query->where('sender_user_id', Auth::id())
                    ->where('recipient_user_id', $user->id);
            })
            ->orWhere(function ($query) use ($user) {
                $query->where('sender_user_id', $user->id)
                    ->where('recipient_user_id', Auth::id());
            })
            ->orderBy('created_at')
            ->get();
    }

    private function markConversationRead(int $userId): void
    {
        $readAt = now()->toIsoString();
        $count = AdminChatMessage::query()
            ->where('sender_user_id', $userId)
            ->where('recipient_user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => $readAt]);

        if ($count > 0) {
            broadcast(new AdminChatMessagesRead($userId, Auth::id(), $readAt))->toOthers();
        }
    }
}
