<?php

namespace App\Models;

use App\Enums\CopyStatus;
use Database\Factories\BookCopyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookCopy extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CopyStatus::class,
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'copy_id');
    }

    public function getBarcode(): string
    {
        return 'COPY-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getBarcodeAttribute(): string
    {
        return $this->getBarcode();
    }

    protected static function newFactory(): BookCopyFactory
    {
        return BookCopyFactory::new();
    }
}
