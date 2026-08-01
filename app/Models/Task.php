<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Scope;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
            'due_date' => 'immutable_datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && $this->status !== TaskStatus::Done;
    }

    #[Scope]
    protected function overdue(Builder $query): void
    {
        $query->where('due_date', '<', now())
            ->where('status', '!=', TaskStatus::Done);
    }

    #[Scope]
    protected function status(Builder $query, TaskStatus $status): void
    {
        $query->where('status', $status);
    }

    #[Scope]
    protected function priority(Builder $query, TaskPriority $priority): void
    {
        $query->where('priority', $priority);
    }

    #[Scope]
    protected function search(Builder $query, string $term): void
    {
        $query->where('title', 'like', "%{$term}%");
    }
}