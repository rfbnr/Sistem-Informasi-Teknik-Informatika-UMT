<?php

namespace App\Policies;

use App\Models\SignatureRequest;
use App\Models\User;
use App\Models\Kaprodi;
use Illuminate\Auth\Access\HandlesAuthorization;

class SignatureRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any signature requests.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the signature request.
     */
    public function view(User $user, SignatureRequest $signatureRequest): bool
    {
        // Requester can always view
        if ($user->id === $signatureRequest->requester_id) {
            return true;
        }

        // Document owner can view
        if ($user->id === $signatureRequest->document->user_id) {
            return true;
        }

        // Signees can view
        return $signatureRequest->signees()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can create signature requests.
     */
    public function create(User $user): bool
    {
        return $user->roles === 'user' || $user->roles === 'admin';
    }

    /**
     * Determine whether the user can update the signature request.
     */
    public function update(User $user, SignatureRequest $signatureRequest): bool
    {
        return $user->id === $signatureRequest->requester_id &&
               $signatureRequest->status === 'draft';
    }

    /**
     * Determine whether the user can delete the signature request.
     */
    public function delete(User $user, SignatureRequest $signatureRequest): bool
    {
        return $user->id === $signatureRequest->requester_id &&
               in_array($signatureRequest->status, ['draft', 'pending']);
    }

    /**
     * Determine whether the user can sign the signature request.
     */
    public function sign(User $user, SignatureRequest $signatureRequest): bool
    {
        // Check if user is a signee
        $signee = $signatureRequest->signees()
            ->where('user_id', $user->id)
            ->first();

        if (!$signee) {
            return false;
        }

        // Check if status allows signing
        if (!in_array($signatureRequest->status, ['pending', 'in_progress'])) {
            return false;
        }

        // Check if signee status allows signing
        if (!in_array($signee->pivot->status, ['pending', 'notified', 'viewed'])) {
            return false;
        }

        // For sequential workflow, check if it's user's turn
        if ($signatureRequest->workflow_type === 'sequential') {
            $nextSigner = $signatureRequest->getNextSigner();
            return $nextSigner && $nextSigner->id === $user->id;
        }

        return true;
    }

    /**
     * Determine whether the user can reject the signature request.
     */
    public function reject(User $user, SignatureRequest $signatureRequest): bool
    {
        return $this->sign($user, $signatureRequest);
    }
}