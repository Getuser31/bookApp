<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $critique
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $books_id
 * @method static \Illuminate\Database\Eloquent\Builder|Critique newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Critique newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Critique query()
 * @method static \Illuminate\Database\Eloquent\Builder|Critique whereBooksId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Critique whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Critique whereCritique($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Critique whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Critique whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Critique extends Model
{
    use HasFactory;
}
