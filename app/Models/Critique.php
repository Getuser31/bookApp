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
 * @property string $critique
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $books_id
 * @method static Builder|Critique newModelQuery()
 * @method static Builder|Critique newQuery()
 * @method static Builder|Critique query()
 * @method static Builder|Critique whereBooksId($value)
 * @method static Builder|Critique whereCreatedAt($value)
 * @method static Builder|Critique whereCritique($value)
 * @method static Builder|Critique whereId($value)
 * @method static Builder|Critique whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Critique extends Model
{
    use HasFactory;
}
