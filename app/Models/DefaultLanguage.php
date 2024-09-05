<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DefaultLanguage extends Model
{
    use HasFactory;

    protected $fillable = ['language'];

    protected $table = 'default_language';

    /**
     * Get the user that owns the default language.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
