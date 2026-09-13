<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public static function referralBonusAmount(): float
    {
        $settings = SiteSetting::getValue('credits', []);

        return (float) ($settings['referral_bonus'] ?? 5);
    }

    public static function credit(User $user, float $amount, string $type, string $description = '', array $extra = []): CreditTransaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $description, $extra) {
            $locked = User::where('id', $user->id)->lockForUpdate()->first();
            $locked->credit_balance = round((float) $locked->credit_balance + $amount, 2);
            $locked->save();

            return CreditTransaction::create([
                'user_id' => $locked->id,
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
                'reference' => $extra['reference'] ?? null,
                'order_id' => $extra['order_id'] ?? null,
                'admin_id' => $extra['admin_id'] ?? null,
                'meta' => $extra['meta'] ?? null,
            ]);
        });
    }

    public static function debit(User $user, float $amount, string $type, string $description = '', array $extra = []): CreditTransaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $description, $extra) {
            $locked = User::where('id', $user->id)->lockForUpdate()->first();
            if ((float) $locked->credit_balance < $amount) {
                throw new \RuntimeException('Insufficient credit balance.');
            }
            $locked->credit_balance = round((float) $locked->credit_balance - $amount, 2);
            $locked->save();

            return CreditTransaction::create([
                'user_id' => $locked->id,
                'amount' => -abs($amount),
                'type' => $type,
                'description' => $description,
                'reference' => $extra['reference'] ?? null,
                'order_id' => $extra['order_id'] ?? null,
                'admin_id' => $extra['admin_id'] ?? null,
                'meta' => $extra['meta'] ?? null,
            ]);
        });
    }

    public static function rewardReferrer(User $newUser): void
    {
        if (! $newUser->referred_by) {
            return;
        }
        $bonus = self::referralBonusAmount();
        if ($bonus <= 0) {
            return;
        }
        $referrer = User::find($newUser->referred_by);
        if (! $referrer) {
            return;
        }
        self::credit($referrer, $bonus, 'referral', 'Referral bonus for '.$newUser->email, [
            'meta' => ['referred_user_id' => $newUser->id],
        ]);
    }
}
