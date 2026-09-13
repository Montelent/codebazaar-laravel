<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TicketAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = SupportTicket::query()->with('user')->orderByDesc('last_reply_at')->orderByDesc('id');

        if ($request->filled('status')) {
            $q->where('status', $request->input('status'));
        }

        $tickets = $q->paginate(30)->withQueryString();

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['messages.user', 'user']);

        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'body' => 'required|string|max:5000',
            'status' => 'nullable|in:open,pending,closed',
        ]);

        $message = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'is_staff' => true,
            'body' => $data['body'],
        ]);

        $ticket->update([
            'status' => $data['status'] ?? 'pending',
            'last_reply_at' => now(),
        ]);

        $this->notifyUserOfReply($ticket, $message);

        return back()->with('success', 'Reply sent'.($this->lastMailOk ? ' and user notified by email' : '').'.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'status' => 'required|in:open,pending,closed',
        ]);
        $ticket->update(['status' => $data['status']]);

        return back()->with('success', 'Ticket marked as '.$data['status'].'.');
    }

    protected bool $lastMailOk = false;

    protected function notifyUserOfReply(SupportTicket $ticket, SupportMessage $message): void
    {
        $this->lastMailOk = false;
        $email = $ticket->user?->email ?: $ticket->guest_email;
        if (! $email) {
            return;
        }

        $name = $ticket->user?->name ?: ($ticket->guest_name ?: 'Customer');
        $url = route('support.show', $ticket);
        $subject = '[Support #'.$ticket->id.'] Re: '.$ticket->subject;
        $body = "Hello {$name},\n\n"
            ."You received a new reply on your support ticket #{$ticket->id}.\n\n"
            ."Subject: {$ticket->subject}\n\n"
            ."--- Support reply ---\n"
            .$message->body."\n"
            ."---------------------\n\n"
            ."Reply online (recommended):\n{$url}\n\n"
            ."You can also reply to this email; include the ticket number #{$ticket->id} in your message so we can match it.\n\n"
            ."— Support Team";

        try {
            Mail::raw($body, function ($mail) use ($email, $name, $subject, $ticket) {
                $mail->to($email, $name)->subject($subject);
                // Help agents match inbound replies
                $mail->getHeaders()->addTextHeader('X-Ticket-ID', (string) $ticket->id);
            });
            $this->lastMailOk = true;
        } catch (\Throwable) {
            $this->lastMailOk = false;
        }
    }
}
