<?php

namespace App\Domain\Ticketing\Actions;

use App\Domain\Ticketing\DTOs\CreateTicketData;
use App\Domain\Ticketing\Models\PipelineStage;
use App\Domain\Ticketing\Models\Ticket;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateTicketAction
{
    public function execute(CreateTicketData $data): Ticket
    {
        return DB::transaction(function () use ($data) {
            // Мягко ищем первую стадию
            $firstStage = PipelineStage::where('pipeline_id', $data->pipeline_id)
                ->orderBy('order_column', 'asc')
                ->first();

            if (! $firstStage) {
                throw new Exception('В этой воронке нет ни одного этапа! Добавьте этапы в настройках.');
            }

            $slaDeadline = null;
            if ($firstStage->sla_max_minutes) {
                $slaDeadline = now()->addMinutes($firstStage->sla_max_minutes);
            }

            $ticket = Ticket::create([
                'branch_id' => $data->branch_id,
                'customer_id' => $data->customer_id,
                'pipeline_id' => $data->pipeline_id,
                'current_stage_id' => $firstStage->id,
                'assigned_technician_id' => empty($data->assigned_technician_id) ? null : $data->assigned_technician_id,
                'device_type' => $data->device_type,
                'device_brand' => $data->device_brand,
                'device_model' => $data->device_model,
                'serial_number' => empty($data->serial_number) ? null : $data->serial_number,
                'defect_description' => $data->defect_description,
                'priority' => $data->priority,
                'estimated_cost' => $data->estimated_cost ?? 0,
                'sla_deadline_at' => $slaDeadline,
            ]);

            $ticket->histories()->create([
                'stage_id' => $firstStage->id,
                'user_id' => auth()->check() ? auth()->id() : null,
                'entered_at' => now(),
            ]);

            return $ticket;
        });
    }
}
