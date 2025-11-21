<?php

declare(strict_types=1);

namespace App\Livewire\Management\Component;

use Livewire\Component;

class StringListInput extends Component
{
    public array $items = [];

    public string $outputArray = '';

    public string $eventName = 'stringListUpdated';

    public string $placeholder = 'Masukkan item...';

    public function mount(array $initialValue = [], string $eventName = 'stringListUpdated', string $placeholder = 'Masukkan item...'): void
    {
        $this->eventName = $eventName;
        $this->placeholder = $placeholder;

        // Parse initial value
        if (! empty($initialValue)) {
            $this->items = array_map(fn ($item) => ['value' => $item], $initialValue);
        } else {
            // Default: 1 baris kosong
            $this->addRow();
        }

        $this->updateOutput();
    }

    public function addRow(): void
    {
        $this->items[] = ['value' => ''];
    }

    public function removeRow(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Re-index array
        $this->updateOutput();
    }

    public function updatedItems(): void
    {
        $this->updateOutput();
    }

    protected function updateOutput(): void
    {
        // Filter items yang tidak kosong
        $validItems = array_filter(
            $this->items,
            fn (array $item) => ! empty(trim($item['value'] ?? ''))
        );

        // Extract values only
        $values = array_values(array_map(
            fn (array $item) => trim($item['value']),
            $validItems
        ));

        // Convert ke JSON string untuk disimpan
        $this->outputArray = json_encode($values, JSON_THROW_ON_ERROR);

        // Emit event ke parent component
        $this->dispatch($this->eventName, $values);
    }

    public function getOutputArray(): array
    {
        $decoded = json_decode($this->outputArray, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function render()
    {
        return view('livewire.management.component.string-list-input');
    }
}
