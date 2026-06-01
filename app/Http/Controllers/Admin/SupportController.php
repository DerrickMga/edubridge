<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Notifications\SupportTicketReplyNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'open');

        $query = SupportTicket::with(['user'])->withCount('replies')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $tickets = $query->paginate(20)->withQueryString();

        $counts = [
            'all'         => SupportTicket::count(),
            'open'        => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'waiting'     => SupportTicket::where('status', 'waiting')->count(),
            'resolved'    => SupportTicket::where('status', 'resolved')->count(),
            'closed'      => SupportTicket::where('status', 'closed')->count(),
        ];

        return view('admin.support.index', compact('tickets', 'counts', 'status'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'replies.user', 'assignee']);
        $admins = User::where('role', 'admin')->get(['id', 'name']);
        return view('admin.support.show', compact('ticket', 'admins'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate(['message' => 'required|string|max:5000']);

        $reply = SupportTicketReply::create([
            'ticket_id'      => $ticket->id,
            'user_id'        => $request->user()->id,
            'message'        => $data['message'],
            'is_admin_reply' => true,
        ]);

        $newStatus = $request->input('status', 'waiting');
        $ticket->update(['status' => $newStatus]);

        // Email the ticket owner
        if ($ticket->user) {
            $ticket->refresh();
            $ticket->user->notify(new SupportTicketReplyNotification($ticket, $reply));
        }

        return back()->with('success', 'Reply sent.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'status'      => 'required|in:open,in_progress,waiting,resolved,closed',
            'admin_notes' => 'nullable|string|max:2000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $previousStatus = $ticket->status;

        $update = ['status' => $data['status']];
        if (isset($data['admin_notes'])) {
            $update['admin_notes'] = $data['admin_notes'];
        }
        if (array_key_exists('assigned_to', $data)) {
            $update['assigned_to'] = $data['assigned_to'] ?: null;
        }
        if ($data['status'] === 'resolved' && ! $ticket->resolved_at) {
            $update['resolved_at'] = now();
        }

        $ticket->update($update);

        // Notify the user when status changes to resolved or closed (without a reply)
        $closingStatuses = ['resolved', 'closed'];
        if (in_array($data['status'], $closingStatuses)
            && ! in_array($previousStatus, $closingStatuses)
            && $ticket->user
        ) {
            $ticket->refresh();
            $closingMessage = $data['status'] === 'resolved'
                ? 'Your support ticket has been marked as resolved. If your issue persists, please open a new ticket or reply to this thread.'
                : 'Your support ticket has been closed. If you need further assistance, please open a new ticket.';

            $syntheticReply = new \App\Models\SupportTicketReply([
                'ticket_id'      => $ticket->id,
                'user_id'        => $request->user()->id,
                'message'        => $closingMessage,
                'is_admin_reply' => true,
            ]);
            $syntheticReply->created_at = now();

            $ticket->user->notify(new SupportTicketReplyNotification($ticket, $syntheticReply));
        }

        return back()->with('success', 'Ticket updated.');
    }

    // Admin email composer
    public function emailComposer()
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role']);
        return view('admin.support.email', compact('users'));
    }

    public function sendEmail(Request $request)
    {
        $data = $request->validate([
            'recipient_type' => 'required|in:single,all_students,all_teachers,all_users,role',
            'recipient_id'   => 'nullable|exists:users,id',
            'role'           => 'nullable|in:student,teacher,admin',
            'subject'        => 'required|string|max:200',
            'body'           => 'required|string|max:20000',
            'reply_to'       => 'nullable|email|max:200',
        ]);

        $recipients = match ($data['recipient_type']) {
            'single'       => User::where('id', $data['recipient_id'])->get(['name', 'email']),
            'all_students' => User::where('role', 'student')->get(['name', 'email']),
            'all_teachers' => User::where('role', 'teacher')->get(['name', 'email']),
            'all_users'    => User::whereIn('role', ['student', 'teacher'])->get(['name', 'email']),
            'role'         => User::where('role', $data['role'])->get(['name', 'email']),
            default        => collect(),
        };

        if ($recipients->isEmpty()) {
            return back()->withErrors(['recipient_type' => 'No recipients found.']);
        }

        $fromName    = 'EduBridge by KMG';
        $fromAddress = 'noreply@kmgvitallinks.co.uk';
        $replyTo     = $data['reply_to'] ?? null;
        $subject     = $data['subject'];
        $rawBody     = $data['body'];

        // Wrap body in a simple branded HTML template
        $html = $this->wrapEmail($rawBody, $subject);

        $sent = 0;
        foreach ($recipients as $recipient) {
            try {
                Mail::html($html, function ($msg) use ($recipient, $subject, $fromAddress, $fromName, $replyTo) {
                    $msg->to($recipient->email, $recipient->name)
                        ->subject($subject)
                        ->from($fromAddress, $fromName);
                    if ($replyTo) {
                        $msg->replyTo($replyTo);
                    }
                });
                $sent++;
            } catch (\Throwable) {
                // continue with remaining recipients
            }
        }

        return back()->with('success', "Email sent to {$sent} of {$recipients->count()} recipient(s).");
    }

    private function wrapEmail(string $body, string $subject): string
    {
        // Convert plain newlines to <br> if body doesn't look like HTML
        if (!str_contains($body, '</')) {
            $body = nl2br(e($body));
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; font-size: 14px; color: #1e293b; background: #f8fafc; margin: 0; padding: 0; }
  .wrapper { max-width: 640px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; }
  .banner { background: linear-gradient(135deg, #7c3aed, #5b21b6); padding: 26px 32px; }
  .banner h1 { color: #fff; font-size: 20px; margin: 0 0 4px; }
  .banner p  { color: #ddd6fe; font-size: 12px; margin: 0; }
  .body { padding: 28px 32px; line-height: 1.8; }
  .footer { background: #f1f5f9; padding: 14px 32px; font-size: 11px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; }
  a { color: #7c3aed; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="banner">
    <h1>EduBridge</h1>
    <p>by KMG Vital Links (Pvt) Ltd &nbsp;&middot;&nbsp; edu.kmgvitallinks.co.uk</p>
  </div>
  <div class="body">
    {$body}
  </div>
  <div class="footer">
    &copy; 2026 KMG Vital Links (Pvt) Ltd &nbsp;&middot;&nbsp; Harare, Zimbabwe &nbsp;&middot;&nbsp; edu.kmgvitallinks.co.uk
  </div>
</div>
</body>
</html>
HTML;
    }
}
