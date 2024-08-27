<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|IndexPreference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IndexPreference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IndexPreference query()
 * @method static \Illuminate\Database\Eloquent\Builder|IndexPreference whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexPreference whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexPreference whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexPreference whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexPreference whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IndexPreference extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];
}
