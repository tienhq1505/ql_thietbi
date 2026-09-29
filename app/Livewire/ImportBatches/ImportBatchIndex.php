<?php

namespace App\Livewire\ImportBatches;

use App\Models\Equipment;
use App\Models\ImportBatch;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class ImportBatchIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public ?ImportBatch $selectedBatch = null;

    public bool $showModal = false;

    public function mount()
    {
        $this->authorize('import', Equipment::class);
    }

    public function viewDetails(int $batchId)
    {
        $this->authorize('import', Equipment::class);
        $this->selectedBatch = ImportBatch::with('importedBy')->findOrFail($batchId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedBatch = null;
    }

    public function render()
    {
        $this->authorize('import', Equipment::class);

        $batches = ImportBatch::with('importedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.import-batches.index', [
            'batches' => $batches,
        ])->layout('components.layouts.app', ['title' => 'Lịch sử Import Thiết bị - MEMS']);
    }
}
