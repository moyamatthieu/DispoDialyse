<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

/**
 * Policy pour les autorisations sur les documents
 */
class DocumentPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir tous les documents
     */
    public function viewAny(User $user): bool
    {
        return $user->can('documents.view');
    }

    /**
     * Déterminer si l'utilisateur peut voir un document
     */
    public function view(User $user, Document $document): bool
    {
        return $user->can('documents.view');
    }

    /**
     * Déterminer si l'utilisateur peut créer/uploader un document
     */
    public function create(User $user): bool
    {
        return $user->can('documents.upload');
    }

    /**
     * Déterminer si l'utilisateur peut modifier un document
     */
    public function update(User $user, Document $document): bool
    {
        // Peut modifier si a la permission ET (est admin OU a créé le document)
        return $user->can('documents.edit') && 
               ($user->isAdmin() || $document->created_by === $user->id);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un document
     */
    public function delete(User $user, Document $document): bool
    {
        // Peut supprimer si a la permission ET (est admin OU a créé le document)
        return $user->can('documents.delete') && 
               ($user->isAdmin() || $document->created_by === $user->id);
    }

    /**
     * Déterminer si l'utilisateur peut télécharger un document
     */
    public function download(User $user, Document $document): bool
    {
        return $user->can('documents.view');
    }
}