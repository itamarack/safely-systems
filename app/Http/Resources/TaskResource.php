<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Task */
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'description'       => $this->description,
            'due_date'          => $this->due_date->toDateString(),
            'priority'          => $this->priority->value, // @phpstan-ignore property.nonObject
            'status'            => $this->status->value, // @phpstan-ignore property.nonObject
            'corrective_action' => $this->corrective_action,
            'user'              => new UserResource($this->whenLoaded('user')),
            'is_overdue'        => $this->isOverdue(),
            'is_due_today'      => $this->isDueToday(),
            'created_at'        => $this->created_at->toIso8601String(),
            'updated_at'        => $this->updated_at->toIso8601String(),
        ];
    }
}
