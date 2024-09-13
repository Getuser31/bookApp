<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property int $id
 * @property string $language
 * @property int $user_id
 * @property int $index_preference_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|UserPreference newModelQuery()
 * @method static Builder|UserPreference newQuery()
 * @method static Builder|UserPreference query()
 * @method static Builder|UserPreference whereCreatedAt($value)
 * @method static Builder|UserPreference whereId($value)
 * @method static Builder|UserPreference whereIndexPreferenceId($value)
 * @method static Builder|UserPreference whereLanguage($value)
 * @method static Builder|UserPreference whereUpdatedAt($value)
 * @method static Builder|UserPreference whereUserId($value)
 * @property int|null $default_language_id
 * @property-read \App\Models\DefaultLanguage|null $defaultLanguage
 * @method static Builder|UserPreference whereDefaultLanguageId($value)
 * @mixin \Eloquent
 */
class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = ['default_language_id', 'user_id', 'index_preference_id'];

    protected $attributes = [
        'default_language_id' => 1, // Default language ID, set to 1
        'index_preference_id' => 1,  // Default index preference ID, set to 1
    ];

    public static function getUserPreference(int $userId): UserPreference|null
    {
        return UserPreference::where('user_id', $userId)->first();
    }

    public function defaultLanguage(): BelongsTo
    {
        return $this->belongsTo(DefaultLanguage::class, 'default_language_id');
    }
}
