<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;

/** Creating customer accounts and moving guest data onto them (web + API share this). */
class CustomerAccounts
{
    /**
     * @param  array{name: string, phone: string, email?: ?string, language?: ?string}  $data
     */
    public function register(array $data): User
    {
        // Double submit / race: the phone may have been registered a moment ago.
        if ($existing = User::where('phone', $data['phone'])->first()) {
            return $existing;
        }

        $email = !empty($data['email']) ? mb_strtolower(trim($data['email'])) : null;
        if ($email && User::where('email', $email)->exists()) {
            $email = null; // taken in the meantime - keep the account, drop the e-mail
        }

        try {
            return User::create([
                'name' => trim($data['name']),
                'phone' => $data['phone'],
                'email' => $email,
                'role' => 'customer',
                'role_id' => Role::where('name', 'user')->value('id'),
                'language' => in_array($data['language'] ?? null, ['en', 'gu'], true) ? $data['language'] : 'en',
                'is_active' => true,
            ]);
        } catch (QueryException $e) {
            if ($existing = User::where('phone', $data['phone'])->first()) {
                return $existing;
            }
            throw $e;
        }
    }

    /** Guest cart rows (by session id) become the user's. */
    public function claimGuestCart(?string $sessionId, User $user): void
    {
        if (!$sessionId) {
            return;
        }
        CartItem::where('session_id', $sessionId)->update([
            'user_id' => $user->id,
            'session_id' => null,
        ]);
    }
}
