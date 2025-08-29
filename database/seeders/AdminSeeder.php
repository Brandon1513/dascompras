<?php

namespace Database\Seeders;

// database/seeders/AdminSeeder.php
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder {
  public function run(): void {
    $user = User::firstOrCreate(
      ['email' => 'admin@dasavena.com'],
      ['name'=>'Admin', 'password'=>bcrypt('password')]
    );
    $user->assignRole('administrador');
  }
}

