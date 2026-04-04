<?php

namespace App\Presentation\Livewire\Settings;

use App\Domain\Ticketing\Models\ChecklistTemplate;
use App\Domain\Ticketing\Models\ChecklistTemplateItem;
use App\Domain\Ticketing\Models\DeviceType;
use App\Domain\Ticketing\Models\PipelineStage;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Справочник: Чек-листы')]
class ChecklistTemplates extends Component
{
    public bool $showCreateForm = false;

    public bool $showEditForm = false;

    public bool $showItemsForm = false;

    // Properties for template
    public ?int $editingTemplateId = null;

    public string $templateName = '';

    public string $templateType = 'diagnostics';

    public ?int $templateStageId = null;

    public ?int $templateDeviceTypeId = null;

    public string $templateDescription = '';

    public bool $templateIsActive = true;

    // Properties for items
    public array $items = [];

    public string $newItemQuestion = '';

    public string $newItemType = 'checkbox';

    public string $newItemOptions = '';

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->templateName = '';
        $this->templateType = 'diagnostics';
        $this->templateStageId = null;
        $this->templateDeviceTypeId = null;
        $this->templateDescription = '';
        $this->templateIsActive = true;
        $this->editingTemplateId = null;
        $this->items = [];
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function openEdit($id)
    {
        $template = ChecklistTemplate::findOrFail($id);
        $this->editingTemplateId = $id;
        $this->templateName = $template->name;
        $this->templateType = $template->type;
        $this->templateStageId = $template->stage_id;
        $this->templateDeviceTypeId = $template->device_type_id;
        $this->templateDescription = $template->description ?? '';
        $this->templateIsActive = $template->is_active;
        $this->showEditForm = true;
    }

    public function openItemsManager($id)
    {
        $template = ChecklistTemplate::with('items')->findOrFail($id);
        $this->editingTemplateId = $id;
        $this->items = $template->items->map(fn ($item) => [
            'id' => $item->id,
            'question' => $item->question,
            'field_type' => $item->field_type,
            'options_json' => $item->options_json,
            'sort_order' => $item->sort_order,
            'is_required' => $item->is_required,
        ])->toArray();
        $this->showItemsForm = true;
    }

    public function saveTemplate()
    {
        $this->validate([
            'templateName' => 'required|string|max:255',
            'templateType' => 'required|string|in:diagnostics,qc,intake,output',
            'templateStageId' => 'nullable|exists:pipeline_stages,id',
            'templateDeviceTypeId' => 'nullable|exists:device_types,id',
        ]);

        if ($this->editingTemplateId) {
            $template = ChecklistTemplate::findOrFail($this->editingTemplateId);
            $template->update([
                'name' => $this->templateName,
                'type' => $this->templateType,
                'stage_id' => $this->templateStageId,
                'device_type_id' => $this->templateDeviceTypeId,
                'description' => $this->templateDescription,
                'is_active' => $this->templateIsActive,
            ]);
        } else {
            ChecklistTemplate::create([
                'name' => $this->templateName,
                'type' => $this->templateType,
                'stage_id' => $this->templateStageId,
                'device_type_id' => $this->templateDeviceTypeId,
                'description' => $this->templateDescription,
                'is_active' => $this->templateIsActive,
            ]);
        }

        $this->dispatch('template-saved');
        $this->resetForm();
        $this->showCreateForm = false;
        $this->showEditForm = false;
    }

    public function addNewItem()
    {
        $this->validate([
            'newItemQuestion' => 'required|string|max:500',
        ]);

        $this->items[] = [
            'question' => $this->newItemQuestion,
            'field_type' => $this->newItemType,
            'options_json' => $this->newItemType === 'select' && $this->newItemOptions ? json_encode(explode(',', $this->newItemOptions)) : null,
            'sort_order' => count($this->items),
            'is_required' => true,
        ];

        $this->newItemQuestion = '';
        $this->newItemOptions = '';
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function saveItems()
    {
        if (! $this->editingTemplateId) {
            return;
        }

        // Delete existing items
        ChecklistTemplateItem::where('template_id', $this->editingTemplateId)->delete();

        // Create new items
        foreach ($this->items as $index => $itemData) {
            ChecklistTemplateItem::create([
                'template_id' => $this->editingTemplateId,
                'question' => $itemData['question'],
                'field_type' => $itemData['field_type'],
                'options_json' => is_array($itemData['options_json']) ? json_encode($itemData['options_json']) : $itemData['options_json'],
                'sort_order' => $index,
                'is_required' => $itemData['is_required'] ?? true,
            ]);
        }

        $this->dispatch('items-saved');
        $this->showItemsForm = false;
        $this->resetForm();
    }

    public function deleteTemplate($id)
    {
        ChecklistTemplate::destroy($id);
        $this->dispatch('template-deleted');
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showCreateForm = false;
        $this->showEditForm = false;
        $this->showItemsForm = false;
    }

    public function render()
    {
        $templates = ChecklistTemplate::with(['stage', 'deviceType', 'items'])->orderBy('sort_order')->get();
        $stages = PipelineStage::all();
        $deviceTypes = DeviceType::all();

        return view('livewire.settings.checklist-templates', compact('templates', 'stages', 'deviceTypes'));
    }
}
