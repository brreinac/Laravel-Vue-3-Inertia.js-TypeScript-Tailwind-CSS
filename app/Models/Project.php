<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $fillable = ['name', 'description', 'status', 'owner_id'];

    protected function casts(): array
    {
        return ['status' => ProjectStatus::class];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasMany<Task, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /** @param Builder<Project> $query */
    public function scopeFilter(Builder $query, ?string $search, ?string $status): void
    {
        $query
            ->when($search, fn (Builder $builder, string $term) => $builder->where('name', 'like', "%{$term}%"))
            ->when($status, fn (Builder $builder, string $value) => $builder->where('status', $value));
    }
}
