<?php

namespace App\Models;

use Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'biography'];

    protected static function newFactory(): AuthorFactory
    {
        return AuthorFactory::new();
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}