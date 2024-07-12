<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Author newModelQuery()
 * @method static Builder|Author newQuery()
 * @method static Builder|Author query()
 * @method static Builder|Author whereCreatedAt($value)
 * @method static Builder|Author whereId($value)
 * @method static Builder|Author whereName($value)
 * @method static Builder|Author whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Author extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
}
