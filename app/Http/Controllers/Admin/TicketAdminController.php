<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'is_staff' => true,
            'body' => $data['body'],
        ]);

        $ticket->update([
            'status' => $data['status'] ?? 'pending',
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'Reply sent.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'status' => 'required|in:open,pending,closed',
        ]);
        $ticket->update(['status' => $data['status']]);

        return back()->with('success', 'Status updated.');
    }
}
