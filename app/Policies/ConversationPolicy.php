<?php
namespace App\Policies;

use App\Models\{Conversation, User};

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $user->isAdmin() || $conversation->user_id === $user->id;
    }
}
