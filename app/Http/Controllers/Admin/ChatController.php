<?php

namespace App\Http\Controllers\Admin;

use App\Events\AdminChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\AdminChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeChat();

        $users = User::where('status', 'active')
            ->orderBy('name')
            ->get();

        $selectedUserId = $request->input('with');
        $selectedUser = $users->where('id', $selectedUserId)->first() ?? $users->first();
        $messages = $selectedUser ? $this->conversationWith($selectedUser) : collect();

        return view('admin.chat', compact('users', 'messages', 'selectedUser'));
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

        $serialized = $messages->map(function (AdminChatMessage $message) {
            return [
                'id' => $message->id,
                'message' => $message->message,
                'sender_name' => $message->sender?->name,
                'sender_id' => $message->sender_user_id,
                'recipient_id' => $message->recipient_user_id,
                'created_at' => $message->created_at?->toIsoString(),
            ];
        })->values();

        return response()->json([
            'messages' => $serialized,
        ]);
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
}
