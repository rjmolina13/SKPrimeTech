<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Municipality;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class GenerateMunicipalUsersSeeder extends Seeder
{
    public function run()
    {
        $municipalities = Municipality::all();
        $output = "Municipal User Credentials\n";
        $output .= "==========================\n\n";

        foreach ($municipalities as $municipality) {
            // Format: [municipality (lower case)][zipcode]@rubyj.xyz
            // Example: virac4800@rubyj.xyz
            // Remove spaces and special chars from municipality name for email
            $cleanName = Str::slug($municipality->name, ''); 
            $email = strtolower($cleanName) . $municipality->code . '@rubyj.xyz';

            // Format: [municipality (first letter upper case)]#[zipcode x2]
            // Example: Virac#48004800
            // Ensure first letter is uppercase, rest lower (or as is? "first letter upper case" usually implies Title Case or ucfirst)
            // Let's use ucfirst(strtolower($name)) to be safe and consistent
            $passwordBaseName = ucfirst(strtolower($municipality->name));
            // Note: Municipality names might have spaces (e.g. San Andres). 
            // The prompt says "[municipality (first letter upper case)]". 
            // Does it mean "San Andres" -> "San Andres" or "Sanandres"?
            // Usually for passwords, spaces are tricky. 
            // But let's follow the pattern literally. 
            // If the user meant the *slug* but capitalized, it would be "Sanandres".
            // If they meant the name, it's "San Andres".
            // Given the email uses the "lower case" version (implied slug/clean), 
            // I will use the *clean* name for the password too to avoid spaces, but capitalized.
            // e.g. Sanandres#48104810
            
            // Re-reading: "municipality (first letter upper case)"
            // Let's interpret this as the name with spaces removed, First letter capitalized.
            // e.g. SanAndres or Sanandres. 
            // Let's go with `ucfirst($cleanName)` (Sanandres) to keep it simple and space-free.
            $passwordBase = ucfirst($cleanName);
            $zipX2 = $municipality->code . $municipality->code;
            $password = $passwordBase . '#' . $zipX2;

            // Create or Update User
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $municipality->name . ' SKMF',
                    'password' => Hash::make($password),
                    'municipality_id' => $municipality->id,
                ]
            );

            // Assign Role
            if (!$user->hasRole('municipal')) {
                $user->assignRole('municipal');
            }

            $output .= "Municipality: {$municipality->name}\n";
            $output .= "Email: {$email}\n";
            $output .= "Password: {$password}\n";
            $output .= "--------------------------\n";
        }

        // Output to file
        File::put(base_path('prime_creds.txt'), $output);
        
        $this->command->info('Municipal users generated. Credentials saved to prime_creds.txt');
    }
}
