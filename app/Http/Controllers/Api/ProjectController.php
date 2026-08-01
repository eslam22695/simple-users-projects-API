<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $projects = $this->projectService->paginateForUser($request->user());

        return $this->success(
            data: ProjectResource::collection($projects)->response()->getData(true),
            message: 'Projects retrieved successfully.',
        );
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->create($request->user(), $request->validated());

        return $this->success(
            data: new ProjectResource($project),
            message: 'Project created successfully.',
            status: Response::HTTP_CREATED,
        );
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return $this->success(
            data: new ProjectResource($project->loadCount('tasks')),
            message: 'Project retrieved successfully.',
        );
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project = $this->projectService->update($project, $request->validated());

        return $this->success(
            data: new ProjectResource($project),
            message: 'Project updated successfully.',
        );
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $this->projectService->delete($project);

        return $this->success(
            message: 'Project deleted successfully.',
            status: Response::HTTP_OK,
        );
    }
}
