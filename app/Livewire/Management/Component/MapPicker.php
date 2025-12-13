<?php

declare(strict_types=1);

namespace App\Livewire\Management\Component;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class MapPicker extends Component
{
    // Coordinates from parent
    public string $latitude = '';

    public string $longitude = '';

    // Location search
    public ?string $selectedLocationId = null;

    public string $selectedLocationName = '';

    public array $locationSearchResults = [];

    // Map settings
    public string $markerId = 'location-marker';

    public int $defaultZoom = 13;

    public bool $draggable = true;

    public bool $showLayerControl = true;

    public string $mapHeight = 'h-100';

    public function updateCoordinates(string $lat, string $lng): void
    {
        $this->latitude = $lat;
        $this->longitude = $lng;

        // Dispatch to parent component
        $this->dispatch('coordinates-updated', [
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
    }

    public function searchLocation(string $value = ''): void
    {
        if (empty($value) || strlen($value) < 3) {
            $this->locationSearchResults = [];

            return;
        }

        try {
            $token = config('services.mapbox.token');
            if (! $token) {
                Log::warning('Mapbox token not configured');
                $this->locationSearchResults = [];

                return;
            }

            // Get proximity bias
            $proximity = (! empty($this->latitude) && ! empty($this->longitude))
                ? "{$this->longitude},{$this->latitude}"
                : 'ip';

            // Call Mapbox Search Box API
            $response = Http::timeout(10)->get('https://api.mapbox.com/search/searchbox/v1/suggest', [
                'q' => $value,
                'access_token' => $token,
                'session_token' => session()->getId(),
                'proximity' => $proximity,
                'language' => 'id',
                'country' => 'id',
                'limit' => 5,
                'types' => 'address,poi,place,street',
            ]);

            if (! $response->successful()) {
                Log::error('Mapbox search failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $this->locationSearchResults = [];

                return;
            }

            $data = $response->json();
            $suggestions = $data['suggestions'] ?? [];

            // Format for x-choices component - ensure clean array structure
            $results = [];
            foreach ($suggestions as $suggestion) {
                $results[] = [
                    'id' => $suggestion['mapbox_id'] ?? '',
                    'name' => $suggestion['name'] ?? 'Unknown',
                    'description' => $suggestion['place_formatted'] ?? '',
                ];
            }

            // Add selected location if exists
            if ($this->selectedLocationId && ! empty($this->selectedLocationName)) {
                $alreadyExists = false;
                foreach ($results as $result) {
                    if ($result['id'] === $this->selectedLocationId) {
                        $alreadyExists = true;
                        break;
                    }
                }

                if (! $alreadyExists) {
                    array_unshift($results, [
                        'id' => $this->selectedLocationId,
                        'name' => $this->selectedLocationName,
                        'description' => '',
                    ]);
                }
            }

            $this->locationSearchResults = $results;
        } catch (Exception $e) {
            Log::error('Location search error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->locationSearchResults = [];
        }
    }

    public function updatedSelectedLocationId(): void
    {
        if (empty($this->selectedLocationId)) {
            return;
        }

        try {
            $token = config('services.mapbox.token');
            if (! $token) {
                return;
            }

            // Retrieve full details with coordinates
            $response = Http::timeout(10)->get("https://api.mapbox.com/search/searchbox/v1/retrieve/{$this->selectedLocationId}", [
                'access_token' => $token,
                'session_token' => session()->getId(),
            ]);

            if (! $response->successful()) {
                Log::error('Mapbox retrieve failed', [
                    'mapbox_id' => $this->selectedLocationId,
                    'status' => $response->status(),
                ]);

                return;
            }

            $data = $response->json();
            $features = $data['features'] ?? [];

            if (! empty($features)) {
                $feature = $features[0];
                $coordinates = $feature['geometry']['coordinates'] ?? null;
                $properties = $feature['properties'] ?? [];

                if ($coordinates && count($coordinates) >= 2) {
                    [$lng, $lat] = $coordinates;

                    $latStr = (string) round($lat, 6);
                    $lngStr = (string) round($lng, 6);

                    // Update coordinates via method (which also dispatches to parent)
                    $this->updateCoordinates($latStr, $lngStr);

                    // Save selected location name
                    $this->selectedLocationName = $properties['name'] ?? $properties['place_formatted'] ?? '';

                    // Update location results
                    $this->locationSearchResults = [[
                        'id' => $this->selectedLocationId,
                        'name' => $this->selectedLocationName,
                        'description' => $properties['place_formatted'] ?? '',
                    ]];

                    // Dispatch event to JavaScript to update map
                    $this->dispatch('location-selected', [
                        'lat' => $lat,
                        'lng' => $lng,
                        'markerId' => $this->markerId,
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('Location retrieve error', [
                'error' => $e->getMessage(),
                'mapbox_id' => $this->selectedLocationId,
            ]);
        }
    }

    public function render(): mixed
    {
        return view('livewire.management.component.map-picker');
    }
}
