<?php

namespace App\Models;

use Database\Factories\DocFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'path'])]
class Doc extends Model
{
    /** @use HasFactory<DocFactory> */
    use HasFactory;

    /**
     * Get the public URL for the stored PDF document.
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn (): string => asset('storage/'.ltrim($this->path, '/')),
        );
    }
}
