<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property string $language
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultLanguage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultLanguage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultLanguage query()
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultLanguage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultLanguage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultLanguage whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefaultLanguage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
