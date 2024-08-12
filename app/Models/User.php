<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Book> $books
 * @property-read int|null $books_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static UserFactory factory($count = null, $state = [])
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @property int|null $role_id
 * @method static Builder|User whereRoleId($value)
 * @property-read \App\Models\Role|null $role
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @mixin Eloquent
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    private mixed $role_id;

    /**
     * @param array $validatedData
     * @return void
     * @throws Exception
     */
    public static function checkIfUserAlreadyExist(array $validatedData): void
    {
        $user = self::find('email', $validatedData['email']);
        if ($user) {
            throw new Exception('User with this email already exists');
        }
        $user = self::find('name', $validatedData['name']);
        if ($user) {
            throw new Exception('User with this name already exists');
        }
    }

    public function checkAdmin(): bool
    {
        $roleId = Role::where('name', 'Admin')->first()->id;

        if ($this->getAttribute('role_id') === $roleId){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return BelongsToMany
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class)->withPivot(['progression']);
    }

    /**
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Find a user by username.
     *
     * @param string $username The username to search for.
     * @return Model|\Illuminate\Database\Query\Builder|User|null The user model or null if not found.
     * @see Model
     * @see \Illuminate\Database\Query\Builder
     * @see User
     */
    public function findByUsername(string $username): Model|Builder|User|null
    {
        return $this->with('role')->where('name', $username)->first();
    }

    public function findByEmail(string $email): Model|Builder|User|null
    {
        return $this->with('role')->where('email', $email)->first();
    }

    /**
     * @throws Exception
     */
    public  static function storeFromRequest(array $validatedData): Model|User
    {
        self::checkIfUserAlreadyExist($validatedData);
        return User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role_id' => $validatedData['role_id'],
        ]);
    }
}
