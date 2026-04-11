<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Municipality;
use App\Models\Barangay;

class ImportCatanduanesSeeder extends Seeder
{
    public function run()
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        // Truncate tables using Laravel's model
        Barangay::truncate();
        Municipality::truncate();

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $this->command->info('Tables truncated.');

        // 1. Load Municipality Codes from Catanduanes.txt
        $municipalityCodes = [];
        $txtPath = base_path('user_temp/Catanduanes.txt');
        if (File::exists($txtPath)) {
            $lines = File::lines($txtPath);
            foreach ($lines as $line) {
                if (str_contains($line, ':')) {
                    [$name, $code] = explode(':', $line);
                    $municipalityCodes[trim($name)] = trim($code);
                }
            }
        }

        // 2. Process CSV
        $csvPath = base_path('.ignore/user_temp/CATANDUANES_LIST-OF-BRGYS-2026.csv');
        if (!File::exists($csvPath)) {
            $this->command->error("CSV file not found at: $csvPath");
            return;
        }

        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file); // Skip header: PROVINCE,CITY/MUNICIPALITY,BARANGAY

        $currentMunicipality = null;
        $currentMunicipalityName = null;

        while (($row = fgetcsv($file)) !== false) {
            // Row structure: [0] => PROVINCE, [1] => CITY/MUNICIPALITY, [2] => BARANGAY
            $province = trim($row[0]);
            $municipalityNameRaw = trim($row[1]);
            $barangayNameRaw = trim($row[2]);

            // Handle Municipality
            if (!empty($municipalityNameRaw)) {
                // Normalize Municipality Name (Title Case)
                // Handle special cases like "VIRAC (Capital)" -> "Virac" if needed, or keep as is.
                // The TXT file has "Virac", CSV has "VIRAC (Capital)".
                // Let's clean it up to match the TXT keys if possible.
                
                $cleanMunicipalityName = Str::title(strtolower($municipalityNameRaw));
                
                // Remove "(Capital)" or similar if it helps matching, but let's try direct matching first
                // Map "Virac (Capital)" to "Virac" for code lookup
                $lookupName = $cleanMunicipalityName;
                if (str_contains(strtolower($lookupName), '(capital)')) {
                    $lookupName = trim(str_ireplace('(capital)', '', $lookupName));
                }

                // Get code from map, default to generated if not found
                // Note: The txt file keys are Case Sensitive in my array, but I'll do case-insensitive lookup
                $code = null;
                foreach ($municipalityCodes as $key => $val) {
                    if (strcasecmp($key, $lookupName) === 0) {
                        $code = $val;
                        break;
                    }
                }
                
                if (!$code) {
                    $code = Str::slug($cleanMunicipalityName); // Fallback
                }

                $currentMunicipality = Municipality::create([
                    'name' => $cleanMunicipalityName,
                    'code' => $code,
                ]);
                $currentMunicipalityName = $cleanMunicipalityName;
            }

            // Handle Barangay
            if (!empty($barangayNameRaw) && $currentMunicipality) {
                $cleanBarangayName = Str::title(strtolower($barangayNameRaw));
                
                // Generate a unique code for the barangay
                // Format: MunCode-BrgySlug-Random
                $brgyCode = $currentMunicipality->code . '-' . Str::slug($cleanBarangayName);
                // Ensure length constraint (50 chars)
                if (strlen($brgyCode) > 45) {
                    $brgyCode = substr($brgyCode, 0, 45);
                }
                
                Barangay::create([
                    'municipality_id' => $currentMunicipality->id,
                    'name' => $cleanBarangayName,
                    'code' => $brgyCode,
                ]);
            }
        }

        fclose($file);
        $this->command->info('Import completed successfully.');
    }
}
