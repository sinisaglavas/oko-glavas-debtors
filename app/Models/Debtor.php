<?php

namespace App\Models;

use Database\Factories\DebtorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['first_name', 'last_name', 'address', 'city', 'phone'])]
class Debtor extends Model
{
    /** @use HasFactory<DebtorFactory> */
    use HasFactory;

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}
