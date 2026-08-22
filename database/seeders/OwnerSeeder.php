<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $ownerEmail = env('OWNER_EMAIL');
        $ownerPassword = env('OWNER_PASSWORD');

          // Find the owner or create a new instance.
        $owner = User::firstOrNew([
            'email' => $ownerEmail,

        ]);
    

     // Set owner data.
        $owner->name = 'OWNER';
        $owner->surname = 'ADMIN';
        $owner->password = Hash::make($ownerPassword);
        $owner->role = 'owner';

        // Save the owner.
        $owner->save();
    }
}
