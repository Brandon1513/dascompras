<?php

namespace Database\Seeders;

// database/seeders/RolesSeeder.php
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder {
  public function run(): void {
    foreach (['administrador','compras','contabilidad','autorizador'] as $r) {
      Role::findOrCreate($r);
    }
  }
}

