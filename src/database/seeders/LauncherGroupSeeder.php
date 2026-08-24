<?php

namespace Database\Seeders;

use App\Models\LauncherGroup;
use Illuminate\Database\Seeder;

class LauncherGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'key' => 'transaksi',
                'label' => 'Transaksi',
                'icon' => 'ti ti-receipt',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'key' => 'master_data',
                'label' => 'Master Data',
                'icon' => 'ti ti-database',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'key' => 'laporan',
                'label' => 'Laporan',
                'icon' => 'ti ti-chart-bar',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'key' => 'sistem',
                'label' => 'Sistem',
                'icon' => 'ti ti-settings',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($groups as $group) {
            LauncherGroup::updateOrCreate(
                ['key' => $group['key']],
                $group
            );
        }
    }
}
