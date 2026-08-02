<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Table('roles')]
#[Fillable(['name', 'description'])]
class Rol extends Model
{
    use HasFactory;

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id')
            ->withPivot('created_at');
    }

    /** @return BelongsToMany<Permiso, $this> */
    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'permission_role', 'role_id', 'permission_id')
            ->withPivot('created_at');
    }
}
