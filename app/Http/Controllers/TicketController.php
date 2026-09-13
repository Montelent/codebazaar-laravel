<?php

namespace App\Http\Controllers;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->orderByDesc('last_reply_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('support.index', compact('tickets'));
    }

    public function create()
    {
        return view('support.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:180',
            'body' => 'required|string|max:5000',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $data['subject'],
            'status' => 'open',
            'priority' => 'normal',
            'last_reply_at' => now(),
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'is_staff' => false,
            'body' => $data['body'],
        ]);

        return redirect()->route('support.show', $ticket)->with('success', 'Ticket opened.');
    }

    public function show(SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);
        $ticket->load('messages.user');

        return view('support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);

        if ($ticket->status === 'closed') {
            return back()->with('error', 'This ticket is closed.');
        }

        $data = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'is_staff' => false,
            'body' => $data['body'],
        ]);

        $ticket->update([
            'status' => 'open',
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'Message sent.');
    }

    protected function authorizeTicket(SupportTicket $ticket): void
    {
        if ((int) $ticket->user_id !== (int) Auth::id()) {
            abort(403);
        }
    }
}
