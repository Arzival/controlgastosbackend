<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsTransaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'savings_fund_id',
        'user_id',
        'type',
        'amount',
        'description',
        'date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    /**
     * Get the savings fund that owns the transaction.
     */
    public function savingsFund(): BelongsTo
    {
        return $this->belongsTo(SavingsFund::class);
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
