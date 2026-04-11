<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Municipality;
use App\Models\Barangay;

class CatanduanesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = base_path('user_temp/cleaned_data/Catanduanes_Combined.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("File not found: {$jsonPath}");
            return;
        }

        $json = File::get($jsonPath);
        $data = json_decode($json, true);

        if (!$data || !isset($data['Municipalities'])) {
            $this->command->error("Invalid JSON data");
            return;
        }

        foreach ($data['Municipalities'] as $munData) {
            $municipalityName = $munData['Municipality'];
            $zipCode = $munData['Zip Code'];

            // Find by name first to handle zip code updates
            $municipality = Municipality::where('name', $municipalityName)->first();

            if ($municipality) {
                // Update zip code if different
                if ($municipality->code !== $zipCode) {
                    $municipality->update(['code' => $zipCode]);
                    $this->command->info("Updated Zip Code for {$municipalityName} to {$zipCode}");
                    
                    // Optionally update existing barangay codes to match new zip prefix?
                    // The user didn't explicitly ask for this, but it keeps data consistent.
                    // However, changing barangay codes might break references if used elsewhere (though they are just strings).
                    // Given the requirement "replace/update data", ensuring consistency is good.
                    // But random part would be lost or needs to be preserved.
                    // Let's preserve random part: oldCode = oldZip + random. newCode = newZip + random.
                    /*
                    foreach ($municipality->barangays as $brgy) {
                        if (strlen($brgy->code) > strlen($zipCode)) {
                            $randomPart = substr($brgy->code, -3); // Assuming 3 digits
                            // Or just take everything after old zip length? 
                            // But old zip length might vary? (usually 4).
                            // Let's assume 3 digit random suffix as per generation logic.
                            $brgy->update(['code' => $zipCode . $randomPart]);
                        }
                    }
                    */
                }
            } else {
                // Create new
                $municipality = Municipality::create([
                    'name' => $municipalityName,
                    'code' => $zipCode,
                ]);
                $this->command->info("Created Municipality {$municipalityName}");
            }

            foreach ($munData['Barangays'] as $brgyName) {
                // Check if barangay already exists for this municipality
                $barangay = Barangay::where('municipality_id', $municipality->id)
                    ->where('name', $brgyName)
                    ->first();

                if (!$barangay) {
                    // Generate unique code: ZipCode + 3 random digits
                    $uniqueCode = $this->generateUniqueBarangayCode($municipality->id, $zipCode);
                    
                    Barangay::create([
                        'municipality_id' => $municipality->id,
                        'name' => $brgyName,
                        'code' => $uniqueCode,
                    ]);
                }
            }
        }

        $this->command->info('Catanduanes data seeded successfully.');
    }

    private function generateUniqueBarangayCode($municipalityId, $zipCode)
    {
        do {
            $randomDigits = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
            $code = $zipCode . $randomDigits;
            $exists = Barangay::where('municipality_id', $municipalityId)
                              ->where('code', $code)
                              ->exists();
        } while ($exists);

        return $code;
    }
}
