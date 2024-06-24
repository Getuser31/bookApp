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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Book> $books
 * @property-read int|null $books_count
 * @method static Builder|Collection newModelQuery()
 * @method static Builder|Collection newQuery()
 * @method static Builder|Collection query()
 * @method static Builder|Collection whereCreatedAt($value)
 * @method static Builder|Collection whereId($value)
 * @method static Builder|Collection whereName($value)
 * @method static Builder|Collection whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Collection extends Model
{
    use HasFactory;

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
