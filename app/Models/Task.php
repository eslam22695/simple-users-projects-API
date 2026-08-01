<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Carbon\CarbonImmutable;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $project_id
 * @property string $title
 * @property string|null $description
 * @property TaskPriority $priority
 * @property TaskStatus $status
 * @property CarbonImmutable|null $due_date
 * @property-read Project $project
 */
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
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

    /**
     * @return BelongsTo<Project, $this>
     */
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

    /**
     * @param  Builder<Task>  $query
     */
    #[Scope]
    protected function overdue(Builder $query): void
    {
        $query->where('due_date', '<', now())
            ->where('status', '!=', TaskStatus::Done);
    }

    /**
     * @param  Builder<Task>  $query
     */
    #[Scope]
    protected function status(Builder $query, TaskStatus $status): void
    {
        $query->where('status', $status);
    }

    /**
     * @param  Builder<Task>  $query
     */
    #[Scope]
    protected function priority(Builder $query, TaskPriority $priority): void
    {
        $query->where('priority', $priority);
    }

    /**
     * @param  Builder<Task>  $query
     */
    #[Scope]
    protected function search(Builder $query, string $term): void
    {
        $escaped = addcslashes($term, '%_\\');
        $query->where('title', 'like', "%{$escaped}%");
    }
}
