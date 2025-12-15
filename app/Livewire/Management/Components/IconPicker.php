<?php

declare(strict_types=1);

namespace App\Livewire\Management\Components;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class IconPicker extends Component
{
    public ?string $selectedIcon = null;

    public string $eventName = 'iconSelected';

    public string $label = 'Pilih Icon';

    public string $placeholder = 'Cari icon...';

    public string $hint = '';

    public Collection $iconOptions;

    // Cache all icons for better performance
    private static ?array $allIconsCache = null;

    public function mount(
        ?string $initialValue = null,
        string $eventName = 'iconSelected',
        string $label = 'Pilih Icon',
        string $placeholder = 'Cari icon...',
        string $hint = ''
    ): void {
        $this->selectedIcon = $initialValue;
        $this->eventName = $eventName;
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->hint = $hint;

        // Initial load with selected icon included
        $this->search();
    }

    /**
     * Get all available icons from iconpark vendor folder
     */
    protected function getAllIcons(): array
    {
        if (self::$allIconsCache !== null) {
            return self::$allIconsCache;
        }

        $iconPath = base_path('vendor/codeat3/blade-iconpark/resources/svg');
        $icons = [];

        if (is_dir($iconPath)) {
            $files = File::files($iconPath);

            foreach ($files as $file) {
                $filename = $file->getFilenameWithoutExtension();

                // Format: iconpark.iconname (sesuai dengan blade icon syntax)
                $iconId = 'iconpark.'.$filename;

                // Create readable name: convert "add-one-o" to "Add One (Outline)"
                $isOutline = str_ends_with($filename, '-o');
                $cleanName = $isOutline ? substr($filename, 0, -2) : $filename;
                $displayName = ucwords(str_replace('-', ' ', $cleanName));

                if ($isOutline) {
                    $displayName .= ' (O)';
                }

                $icons[] = [
                    'id' => $iconId,
                    'name' => $displayName,
                    'filename' => $filename,
                ];
            }
        }

        // Sort by name
        usort($icons, fn ($a, $b) => strcmp($a['name'], $b['name']));

        self::$allIconsCache = $icons;

        return $icons;
    }

    /**
     * Search icons - called on mount and while typing
     */
    public function search(string $value = ''): void
    {
        $allIcons = $this->getAllIcons();

        // If we have a selected icon, make sure it's included
        $selectedOption = collect([]);
        if ($this->selectedIcon) {
            $selectedOption = collect($allIcons)
                ->filter(fn ($icon) => $icon['id'] === $this->selectedIcon)
                ->values();
        }

        // Search and limit results
        $searchResults = collect($allIcons)
            ->when($value !== '', function (Collection $collection) use ($value) {
                return $collection->filter(function ($icon) use ($value) {
                    $searchValue = strtolower($value);

                    return str_contains(strtolower($icon['name']), $searchValue)
                        || str_contains(strtolower($icon['filename']), $searchValue);
                });
            })
            ->take(20) // Limit to 20 results
            ->values();

        // Merge selected option with search results
        $this->iconOptions = $searchResults
            ->merge($selectedOption)
            ->unique('id')
            ->values();
    }

    /**
     * When icon is selected, dispatch event to parent
     */
    public function updatedSelectedIcon(): void
    {
        $this->dispatch($this->eventName, $this->selectedIcon);
    }

    public function render(): mixed
    {
        return view('livewire.management.components.icon-picker');
    }
}
