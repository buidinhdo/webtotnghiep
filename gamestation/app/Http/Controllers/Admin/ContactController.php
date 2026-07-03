<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\UserNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $contacts = ContactMessage::oldest()->paginate(15);
        return view('admin.contacts.index', compact('contacts'));
    }

    public function show(ContactMessage $contact)
    {
        return view('admin.contacts.show', compact('contact'));
    }

    public function reply(Request $request, ContactMessage $contact)
    {
        $validated = $request->validate([
            'reply' => 'required|string|min:5',
        ]);

        $contact->update([
            'admin_reply' => $validated['reply'],
            'admin_replied_at' => now(),
            'status' => 'replied'
        ]);

        // Send email to user
        try {
            Mail::raw("Cảm ơn bạn đã liên hệ với chúng tôi.\n\nPhản hồi của chúng tôi:\n\n" . $validated['reply'], function ($message) use ($contact) {
                $message->to($contact->email)
                        ->subject("Phản hồi về liên hệ của bạn: " . $contact->subject);
            });
        } catch (\Exception $e) {
            // Log email error but don't fail the reply
        }

        // Notify related users so reply appears in bell + notifications page.
        $email = mb_strtolower(trim((string) $contact->email));
        $users = User::query()
            ->when($contact->user_id, fn ($q) => $q->orWhere('id', $contact->user_id))
            ->orWhereRaw('LOWER(email) = ?', [$email])
            ->get();

        if ($users->isNotEmpty()) {
            if (!$contact->user_id) {
                $contact->update(['user_id' => $users->first()->id]);
            }

            foreach ($users as $user) {
                $notificationBody = "Liên hệ #{$contact->id}\nChủ đề: {$contact->subject}\n\nNội dung phản hồi: {$validated['reply']}";

                $alreadyExists = $user->userNotifications()
                    ->where('title', 'Phản hồi từ quản lý liên hệ')
                    ->where('body', 'like', "Liên hệ #{$contact->id}%")
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                $user->userNotifications()->create([
                    'title' => 'Phản hồi từ quản lý liên hệ',
                    'body' => $notificationBody,
                ]);
            }
        }

        return redirect()->route('admin.contacts.show', $contact)->with('success', 'Phản hồi đã được gửi qua email và tạo thông báo.');
    }

    public function destroy(ContactMessage $contact)
    {
        $contact->delete();
        return redirect()->route('admin.contacts.index')->with('success', 'Liên hệ đã được xóa.');
    }
}

