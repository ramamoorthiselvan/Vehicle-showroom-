<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelShowroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_name',
        'year',
        'price',
        'image',
        'status',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price' => 'decimal:2',
            'status' => 'boolean',
            'display_order' => 'integer',
        ];
    }
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('id');
    }

    public function getTitleAttribute(): string
    {
        return "{$this->year} {$this->model_name}";
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$'.number_format((float) $this->price, 0);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }
}
