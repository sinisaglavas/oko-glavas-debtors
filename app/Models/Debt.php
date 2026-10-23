<?php

namespace App\Models;

use Database\Factories\DebtFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['debtor_id', 'store_id', 'incurred_on', 'original_amount_minor', 'installment_count', 'created_by'])]
class Debt extends Model
{
    /** @use HasFactory<DebtFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'incurred_on' => 'date',
            'original_amount_minor' => 'integer',
            'installment_count' => 'integer',
        ];
    }

    public function debtor(): BelongsTo
    {
        return $this->belongsTo(Debtor::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->whereBetween('incurred_on', ["{$year}-01-01", "{$year}-12-31"]);
    }

    public function paidAmountMinor(): int
    {
        return (int) $this->payments()->whereNull('voided_at')->sum('amount_minor');
    }

    public function outstandingAmountMinor(): int
    {
        return max(0, $this->original_amount_minor - $this->paidAmountMinor());
    }

    public function isSettled(): bool
    {
        return $this->outstandingAmountMinor() === 0;
    }

    /** @return array<int, int> */
    public function installmentScheduleMinor(): array
    {
        $base = intdiv($this->original_amount_minor, $this->installment_count);
        $last = $this->original_amount_minor - ($base * ($this->installment_count - 1));

        return [...array_fill(0, $this->installment_count - 1, $base), $last];
    }
}
