<?php
require '/var/www/html/src/vendor/autoload.php';
$app = require '/var/www/html/src/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$role = Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
$role->syncPermissions(Spatie\Permission\Models\Permission::all());
echo 'Super Admin perms: ' . $role->permissions()->count() . PHP_EOL;
