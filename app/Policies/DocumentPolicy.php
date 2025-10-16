<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Models\Kaprodi;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        // Owner can always view
        if ($user->id === $document->user_id) {
            return true;
        }

        // Check if user is involved in any signature request for this document
        return $document->signatureRequests()
            ->whereHas('signees', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();
    }

    /**
     * Determine whether the user can create documents.
     */
    public function create(User $user): bool
    {
        return $user->roles === 'user' || $user->roles === 'admin';
    }

    /**
     * Determine whether the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }

    /**
     * Determine whether the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->id === $document->user_id && $document->status === 'uploaded';
    }

    /**
     * Determine whether the user can restore the document.
     */
    public function restore(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }

    /**
     * Determine whether the user can permanently delete the document.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        return $user->roles === 'admin';
    }
}