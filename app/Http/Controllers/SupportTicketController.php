<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->withCount('replies')
            ->latest()
            ->paginate(15);

        return view('support.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject'  => 'required|string|max:200',
            'message'  => 'required|string|max:5000',
            'category' => 'required|in:technical,billing,course,complaint,general',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $ticket = SupportTicket::create([
            ...$data,
            'user_id'       => $request->user()->id,
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'status'        => 'open',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'ticket_number' => $ticket->ticket_number]);
        }

        return redirect()->route('support.show', $ticket)
            ->with('success', 'Ticket #' . $ticket->ticket_number . ' submitted. We will get back to you shortly.');
    }

    public function show(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === $request->user()->id, 403);
        $ticket->load(['replies.user']);
        return view('support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === $request->user()->id, 403);
        abort_unless($ticket->isOpen(), 422, 'This ticket is closed.');

        $data = $request->validate(['message' => 'required|string|max:5000']);

        SupportTicketReply::create([
            'ticket_id'      => $ticket->id,
            'user_id'        => $request->user()->id,
            'message'        => $data['message'],
            'is_admin_reply' => false,
        ]);

        // Re-open if it was in "waiting" state
        if ($ticket->status === 'waiting') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Reply sent.');
    }
}
