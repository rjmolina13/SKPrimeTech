<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

class NavbarWeather extends Component
{
    public $lat;
    public $lon;
    public $municipalityName;
    public $apiKey;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->apiKey = env('OPENWEATHER_API_KEY');
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        // Default to Virac (Catanduanes Capital)
        // Virac Coordinates: 13.5892, 124.2372
        $defaultLat = 13.5892;
        $defaultLon = 124.2372;
        $this->municipalityName = 'Virac';
        $this->lat = $defaultLat;
        $this->lon = $defaultLon;

        if ($user) {
            if ($user->hasRole(['super_admin', 'admin'])) {
                // Admin/Superadmin defaults to Virac
                $this->municipalityName = 'Virac';
                $this->lat = $defaultLat;
                $this->lon = $defaultLon;
            } else {
                // Try to get user's municipality
                // Assuming relationship exists: $user->municipality or via barangay
                $municipality = null;
                
                if ($user->municipality) {
                    $municipality = $user->municipality;
                } elseif ($user->barangay && $user->barangay->municipality) {
                    $municipality = $user->barangay->municipality;
                }

                if ($municipality) {
                    $this->municipalityName = $municipality->name;
                    $coords = $this->getCoordinates($municipality->name);
                    $this->lat = $coords['lat'];
                    $this->lon = $coords['lon'];
                }
            }
        }
    }

    protected function getCoordinates($name)
    {
        // Coordinates for Catanduanes Municipalities
        $coordinates = [
            'Virac' => ['lat' => 13.5892, 'lon' => 124.2372],
            'San Andres' => ['lat' => 13.5969, 'lon' => 124.0950],
            'Caramoran' => ['lat' => 13.9786, 'lon' => 124.1625],
            'Pandan' => ['lat' => 14.0453, 'lon' => 124.1694],
            'Viga' => ['lat' => 13.8822, 'lon' => 124.3047],
            'Panganiban' => ['lat' => 13.9031, 'lon' => 124.2981],
            'Bagamanoc' => ['lat' => 13.9392, 'lon' => 124.2872],
            'Gigmoto' => ['lat' => 13.7797, 'lon' => 124.3917],
            'Baras' => ['lat' => 13.6842, 'lon' => 124.3739],
            'Bato' => ['lat' => 13.6014, 'lon' => 124.2967],
            'San Miguel' => ['lat' => 13.6425, 'lon' => 124.2325],
        ];

        return $coordinates[$name] ?? ['lat' => 13.5892, 'lon' => 124.2372]; // Default to Virac
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.navbar-weather');
    }
}
