<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotMessage;
use App\Models\User;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        // Fetch users who have chatted, paginated
        $users = User::whereHas('chatbotMessages')
            ->withCount(['chatbotMessages'])
            ->paginate(15);

        // Fetch latest message for each user
        foreach ($users as $user) {
            $user->latestMessage = ChatbotMessage::where('user_id', $user->id)
                ->latest()
                ->first();
        }

        return view('admin.chatbot.index', compact('users'));
    }

    public function show(User $user)
    {
        $messages = ChatbotMessage::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.chatbot.show', compact('user', 'messages'));
    }

    public function reply(Request $request, User $user)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ChatbotMessage::create([
            'user_id' => $user->id,
            'sender' => 'admin',
            'message' => trim($request->input('message')),
        ]);

        return redirect()->route('admin.chatbot.show', $user)->with('success', 'Đã gửi phản hồi trực tiếp đến khách hàng.');
    }

    public function destroy(User $user)
    {
        ChatbotMessage::where('user_id', $user->id)->delete();

        return redirect()->route('admin.chatbot.index')->with('success', 'Đã xóa toàn bộ lịch sử hội thoại của khách hàng này.');
    }
}
