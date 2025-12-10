<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

/**
 * Policy pour les autorisations sur la messagerie interne
 */
class MessagePolicy
{
    /**
     * Déterminer si l'utilisateur peut voir tous les messages
     */
    public function viewAny(User $user): bool
    {
        return $user->can('messages.view');
    }

    /**
     * Déterminer si l'utilisateur peut voir un message
     */
    public function view(User $user, Message $message): bool
    {
        // Peut voir si c'est l'expéditeur ou le destinataire
        return $user->can('messages.view') && 
               ($message->sender_id === $user->id || $message->recipient_id === $user->id);
    }

    /**
     * Déterminer si l'utilisateur peut créer/envoyer un message
     */
    public function create(User $user): bool
    {
        return $user->can('messages.send');
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un message
     */
    public function delete(User $user, Message $message): bool
    {
        // Peut supprimer si c'est le destinataire (suppression de sa boîte de réception)
        return $user->can('messages.view') && $message->recipient_id === $user->id;
    }

    /**
     * Déterminer si l'utilisateur peut répondre à un message
     */
    public function reply(User $user, Message $message): bool
    {
        // Peut répondre si c'est le destinataire
        return $user->can('messages.send') && $message->recipient_id === $user->id;
    }

    /**
     * Déterminer si l'utilisateur peut marquer un message comme lu
     */
    public function markAsRead(User $user, Message $message): bool
    {
        // Peut marquer comme lu si c'est le destinataire
        return $user->can('messages.view') && $message->recipient_id === $user->id;
    }
}