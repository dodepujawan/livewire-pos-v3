# POS SPA Framework — Great Documentation

> **Tujuan:** Dokumentasi lengkap, praktis, dan copy-paste ready untuk framework POS SPA. Semua alur, file penting, kode inti, dan langkah implementasi launcher ditulis satu tempat agar mudah diikuti maupun dijadikan acuan project baru.

---

## 1. Status Framework

```text
Komponen                     Status
---------------------------  --------
Laravel 12                   ✅
Livewire 4 MFC               ✅
Route Synchronization        ✅
System Route                 ✅
Dynamic Menu                 ✅
Dynamic Sidebar              ✅
Permission Synchronization   ✅
Permission Matrix            ✅
Role Management              ✅
Dynamic Authorization        ✅
Config Export/Import         ✅
Desktop Launcher             ✅
Documentation                ✅
API Support                  ⬜ not needed ATM
Framework Generator          ⬜ not needed
```

---

## 2. Prinsip Dasar

Framework menggunakan **route sebagai sumber kebenaran**.

```text
Laravel Route
     ↓
Route Sync
     ↓
system_routes
     ↓
PermissionNameService
     ↓
Spatie Permission
     ↓
Role
     ↓
Authorization
     ↓
Sidebar / Launcher
```

Menu disimpan terpisah dari route sehingga administrator dapat mengatur struktur sidebar tanpa mengubah kode route.

---

## 3. Struktur Folder Penting

```text
app/
├── Console/Commands/
│   ├── RouteSyncCommand.php
│   ├── PermissionSyncCommand.php
│   ├── FrameworkConfigExportCommand.php
│   └── FrameworkConfigImportCommand.php
├── Http/Middleware/
│   └── PermissionMiddleware.php
├── Models/
│   ├── Menu.php
│   ├── SystemRoute.php
│   └── LauncherGroup.php
├── Services/
│   ├── PermissionNameService.php
│   ├── PermissionMatrixService.php
│   └── PermissionScannerService.php
├── Support/
│   └── AuthorizesRoute.php
└── Http/Controllers/Auth/LoginController.php

database/
├── migrations/
│   ├── create_menus_table.php
│   ├── create_system_routes_table.php
│   └── create_launcher_groups_table.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── MenuSeeder.php
    ├── LauncherGroupSeeder.php
    └── SuperAdminSeeder.php

resources/views/
├── layouts/
│   ├── app.blade.php
│   ├── navbar.blade.php
│   └── sidebar.blade.php
├── components/
│   ├── ⚡sidebar/
│   │   ├── sidebar.php
│   │   └── sidebar.blade.php
│   └── ⚡launcher/
│       ├── launcher.php
│       └── launcher.blade.php
├── pages/
│   ├── master/
│   │   ├── ⚡menu-list/
│   │   ├── ⚡menu-create/
│   │   ├── ⚡menu-edit/
│   │   └── ⚡launcher-group-manager/
│   ├── system/
│   └── transaksi/
└── dashboard/
    └── index.blade.php

routes/
└── web.php

docs/
├── FRAMEWORK_PERMISSION_DOCUMENTATION.md
├── PROJECT_RULES_v2.md
└── DESKTOP_LAUNCHER_MILESTONE.MD
```

---

## 4. Route Convention

Route merupakan fondasi framework.

```php
Route::livewire(
    '/barang',
    'pages::master.barang-list'
)->name('master.barang.list');
```

Konvensi:

```text
master.barang.list
│      │      │
│      │      └── Page Action
│      └───────── Resource
└──────────────── Resource
```

Jumlah segmen tidak dibatasi. Segmen terakhir dianggap sebagai action halaman.

Contoh:

```text
master.barang.list
master.barang.create
master.barang.edit

transaksi.penjualan.list
transaksi.penjualan.create
transaksi.penjualan.show
transaksi.penjualan.edit
```

Untuk halaman yang ingin menjadi sumber menu/sidebar, gunakan action `list`.

---

## 5. Route Synchronization

File: `app/Console/Commands/RouteSyncCommand.php`

Command:
```bash
php artisan framework:route-sync
```

Fungsi:
1. Membaca seluruh named route Laravel
2. Mengabaikan route internal tertentu
3. Menyimpan route ke `system_routes`
4. Membuat `display_name`
5. Memperbarui route yang sudah ada
6. Menghapus route yang sudah tidak ada jika route tersebut tidak digunakan oleh menu

Route internal yang diabaikan:
```text
livewire.*
ignition.*
debugbar.*
sanctum.*
```

Display name dibuat dari route name:
```text
master.barang.list → Master Barang List
```

Command tidak menghapus `system_routes` yang masih memiliki menu.

---

## 6. SystemRoute Model

File: `app/Models/SystemRoute.php`

Kolom utama:
- `route_name`
- `display_name`

Relasi:
```php
public function menus(): HasMany
```

`system_routes` merupakan daftar halaman yang ditemukan framework.

---

## 7. Dynamic Menu

File: `app/Models/Menu.php`

Kolom:
- `parent_id`
- `system_route_id`
- `title`
- `icon`
- `sort_order`
- `is_sidebar`
- `launcher_group`

Relasi:
```text
Menu
├── parent
├── children
├── systemRoute
└── launcherGroup
```

Aturan:
- Root menu dapat tidak memiliki route
- Child menu dapat mempunyai parent
- Route disimpan melalui `system_route_id`
- Judul menu disimpan di `menus.title`
- Icon disimpan di database
- Urutan disimpan di `sort_order`
- `is_sidebar` menentukan apakah menu digunakan pada sidebar
- `launcher_group` menentukan apakah menu muncul di launcher dan group nya apa

Struktur contoh:
```text
Master
├── Barang
└── ...

Transaksi
└── Penjualan
```

---

## 8. Menu Seeder

File: `database/seeders/MenuSeeder.php`

Seeder membuat contoh menu:
```text
Dashboard
Master
└── Barang
Transaksi
└── Penjualan
```

MenuSeeder membaca `SystemRoute` untuk mencari route ID.

**Penting:** `MenuSeeder` saat ini masih berisi struktur menu contoh project dan perlu disesuaikan apabila framework dipakai untuk project baru. Jangan menganggap MenuSeeder sebagai generator menu otomatis.

---

## 9. Sidebar

Sidebar menggunakan data `Menu` dari database.

Kemampuan:
- Database driven
- Parent / Child
- Sort order
- Icon
- Active menu
- Active parent
- Expand / collapse
- Permission filtering
- Livewire navigation

Authorization sidebar menggunakan `PermissionNameService` dan authorization layer framework.

---

## 10. Permission Naming

File: `app/Services/PermissionNameService.php`

Service ini merupakan pusat konversi:
```text
Route Name → Permission Name
```

Mapping:
```text
list    → view
show    → view

create  → create
store   → create

edit    → update
update  → update

destroy → delete
delete  → delete

print   → print
export  → export
import  → import
```

Contoh:
```text
master.barang.list → master.barang.view
master.barang.edit → master.barang.update
transaksi.penjualan.show → transaksi.penjualan.view
```

Jika action tidak ada pada mapping, action tersebut dipertahankan.

---

## 11. Permission Synchronization

File: `app/Console/Commands/PermissionSyncCommand.php`

Command:
```bash
php artisan framework:permission-sync
```

Alur:
```text
system_routes
     ↓
PermissionNameService
     ↓
Route Permissions

PermissionScannerService
     ↓
Additional Permissions

Route Permissions + Additional Permissions
     ↓
unique
     ↓
Spatie Permission
```

Command menggunakan guard: `web`

- Permission yang belum ada akan dibuat
- Permission yang sudah ada akan dipertahankan
- Permission yang sudah tidak berasal dari sumber sinkronisasi akan dihapus hanya jika permission tersebut tidak memiliki role
- Permission yang masih digunakan oleh role tidak dihapus oleh cleanup tersebut

---

## 12. Additional Permissions

Tidak semua permission merupakan halaman.

Contoh action bisnis:
```text
delete, print, export, import, approval, posting, closing, cancel
```

Untuk action bisnis yang bukan Page, deklarasikan:
```php
protected array $additionalPermissions = [
    'master.barang.delete',
    'master.barang.export',
];
```

PermissionScannerService akan mencari deklarasi tersebut.

---

## 13. PermissionScannerService

File: `app/Services/PermissionScannerService.php`

Scanner membaca handler yang digunakan oleh route.

Didukung:
- Livewire MFC Component
- Laravel Controller

### Livewire
Scanner membaca metadata `livewire_component`, kemudian mencari file component MFC, dan mengekstrak `$additionalPermissions`.

Scanner tidak melakukan instantiate anonymous Livewire component. Ini penting karena arsitektur Livewire MFC menggunakan `new class extends Component`.

### Controller
Scanner menggunakan reflection untuk membaca property `$additionalPermissions`.

---

## 14. Permission Matrix

File: `app/Services/PermissionMatrixService.php`

Permission Matrix mengelompokkan permission berdasarkan resource.

Contoh:
```text
master.barang
├── view
├── create
├── update
├── delete
├── print
├── export
└── import
```

Urutan action: view, create, update, delete, print, export, import.

Permission internal seperti login, storage, dan resource tertentu yang tidak relevan untuk matrix dikecualikan melalui `ignoredResources`.

---

## 15. Authorization

File utama: `app/Http/Middleware/PermissionMiddleware.php`

Alias didaftarkan di: `bootstrap/app.php`

Alias: `permission`

Penggunaan:
```php
Route::prefix('master')
    ->middleware(['auth', 'permission'])
    ->group(function () {
        ...
    });
```

Authorization membaca nama route:
```text
master.barang.list
        ↓
PermissionNameService
        ↓
master.barang.view
        ↓
auth()->user()->can(...)
```

Jika user tidak memiliki permission: **403 Forbidden**

Jika route tidak memiliki route name, middleware saat ini melewati authorization tersebut. Karena itu route yang menggunakan authorization framework harus mempunyai named route.

---

## 16. AuthorizesRoute Trait

File: `app/Support/AuthorizesRoute.php`

Trait menyediakan `authorizeRoute()` menggunakan route name dan `PermissionNameService` untuk memeriksa permission user. Trait ini digunakan ketika authorization perlu dilakukan dari dalam component/class, terpisah dari route middleware.

---

## 17. Route Middleware vs Additional Permission

Gunakan middleware untuk authorization terhadap Page:
```text
master.barang.list
master.barang.create
master.barang.edit
```

Gunakan `additionalPermissions` untuk action bisnis yang bukan Page:
```text
master.barang.delete
master.barang.export
transaksi.penjualan.cancel
transaksi.penjualan.posting
```

Jangan membuat mapping route → permission baru di component. Gunakan `PermissionNameService` sebagai pusat mapping.

---

## 18. Membuat Page Baru

Contoh membuat halaman Barang:

```text
resources/views/pages/master/
└── ⚡barang-list/
    ├── barang-list.php
    └── barang-list.blade.php
```

Route:
```php
Route::livewire('/barang', 'pages::master.barang-list')
    ->name('master.barang.list');
```

Kemudian jalankan:
```bash
php artisan framework:route-sync
php artisan framework:permission-sync
```

Hasil:
```text
system_routes: master.barang.list
permission: master.barang.view
```

Setelah permission tersedia, menu dapat dibuat melalui menu management.

---

## 19. Membuat Action Bisnis

Misalnya ada tombol Export. Export bukan Page baru.

Deklarasikan:
```php
protected array $additionalPermissions = [
    'master.barang.export',
];
```

Kemudian:
```bash
php artisan framework:permission-sync
```

Permission `master.barang.export` akan tersedia untuk Role Permission Matrix.

---

## 20. Testing

Testing core saat ini menggunakan PHPUnit/Pest Laravel.

Menjalankan seluruh test:
```bash
php artisan test
```

Current core test coverage:
- Application redirect
- Route → Permission mapping
- Multiple route segments
- Permission middleware allow
- Permission middleware deny

Current result: 10 passed, 11 assertions.

Testing tidak dimaksudkan untuk mengejar jumlah test sebanyak mungkin. Tujuannya adalah menjaga bagian framework yang paling kritis agar perubahan berikutnya tidak merusaknya.

---

## 21. Deployment / Clone Checklist

Untuk project baru, prinsipnya:

```text
1. Install dependency
2. Configure .env
3. Configure database
4. Run migrations
5. Seed required data
6. Synchronize routes
7. Synchronize permissions
8. Configure initial roles/users
9. Configure menus
10. Run tests
```

Command utama:
```bash
php artisan migrate
php artisan db:seed
php artisan framework:route-sync
php artisan framework:permission-sync
php artisan test
```

**Catatan penting:** automation lengkap untuk deployment belum menjadi bagian final saat ini. `DatabaseSeeder`, `MenuSeeder`, `RoleSeeder`, dan `UserSeeder` masih memiliki peran yang berbeda dan sebagian masih membutuhkan penyesuaian untuk project baru.

---

## 22. Framework Configuration Sync

Framework menyediakan mekanisme untuk membawa konfigurasi route, permission, dan menu dari environment development ke environment deployment tanpa memasukkan database secara langsung.

### Export dari Local

Setelah route, permission, dan menu selesai dikonfigurasi:
```bash
php artisan framework:route-sync
php artisan framework:permission-sync
php artisan framework:config-export
```

### Import ke Target
```bash
git pull
php artisan framework:config-import
```

Prinsip Sinkronisasi:
- **Additive & safe**: Menu yang sudah ada → Skip. Menu belum ada → Create.
- **Tidak menghapus** konfigurasi menu yang sudah diatur administrator pada environment tujuan.
- **Idempotency**: Command import dapat dijalankan lebih dari satu kali.

---

## 23. Production Checklist

```text
[ ] .env production sudah benar
[ ] APP_DEBUG=false
[ ] Database production benar
[ ] Migration selesai
[ ] Route sync selesai
[ ] Permission sync selesai
[ ] Role sudah dibuat
[ ] User sudah memiliki role
[ ] Menu sudah disusun
[ ] Permission middleware aktif
[ ] php artisan test berhasil
```

Jangan menggunakan password contoh dari development untuk production.

---

## 24. Known Limitations

Framework saat ini belum mencakup:
```text
API authorization layer
Framework generator
CRUD generator
Automatic deployment wizard
Automatic seeder orchestration
GitHub release automation
Version management
```

API tidak merupakan requirement untuk framework web/Livewire saat ini. API dapat ditambahkan sebagai layer terpisah di masa depan tanpa mengubah konsep utama route/menu web framework.

---

## 25. Framework Decision Rules

Sebelum menambahkan fitur framework baru, pertimbangkan:
```text
Apakah fitur benar-benar dibutuhkan?
Apakah ada solusi lebih sederhana?
Apakah scalable?
Apakah mudah di-maintain?
Apakah perubahan 2 tahun lagi dapat dilakukan tanpa membongkar arsitektur?
```

Framework tidak dibuat untuk memiliki sebanyak mungkin file atau service. Setiap abstraction harus memiliki alasan.

---

## 26. Important Rules — Jangan Dilupakan

### Route
```text
Nama route adalah fondasi authorization.
```

### Permission
```text
Jangan membuat convertPermission() baru.
Gunakan PermissionNameService.
```

### Page
```text
Page action terakhir menentukan mapping permission.
```

### Business Action
```text
Gunakan additionalPermissions.
```

### Sidebar
```text
Menu berasal dari database.
```

### SystemRoute
```text
Jangan menghapus route yang masih digunakan Menu.
```

### Permission Cleanup
```text
Permission yang masih digunakan Role tidak dihapus oleh cleanup.
```

---

## 27. Quick Command Reference

```bash
# Framework
php artisan framework:route-sync
php artisan framework:permission-sync
php artisan framework:config-export
php artisan framework:config-import
php artisan test

# Livewire
php artisan make:livewire pages::master.nama-component --mfc

# Database
php artisan migrate
php artisan db:seed
```

---

## 28. File Penting

```text
app/Console/Commands/RouteSyncCommand.php
app/Console/Commands/PermissionSyncCommand.php
app/Console/Commands/FrameworkConfigExportCommand.php
app/Console/Commands/FrameworkConfigImportCommand.php

app/Services/PermissionNameService.php
app/Services/PermissionMatrixService.php
app/Services/PermissionScannerService.php

app/Http/Middleware/PermissionMiddleware.php
app/Support/AuthorizesRoute.php

app/Models/SystemRoute.php
app/Models/Menu.php
app/Models/LauncherGroup.php

bootstrap/app.php
routes/web.php
```

---

## 29. Arsitektur Framework (Visual)

```text
                  ROUTES (web.php)
                     │
                     ▼
           RouteSyncCommand
                     │
                     ▼
              system_routes
                     │
           ┌─────────┴─────────┐
           ▼                   ▼
        MENUS          PermissionNameService
           │                   │
           ▼                   ▼
        SIDEBAR           PERMISSIONS
      + LAUNCHER                 │
                                ▼
                              ROLES
                                │
                                ▼
                           AUTHORIZATION
                        ┌───────┴───────┐
                        ▼               ▼
                  Middleware       Livewire Trait
```

---

## 30. Implementasi Desktop Launcher (Step by Step)

Bagian ini adalah dokumentasi implementasi launcher yang sudah dilakukan. Gunakan sebagai template untuk project baru.

### M1 — Database & Model

#### Migration 1: add_launcher_group_to_menus_table

File: `src/database/migrations/2026_08_18_150000_add_launcher_group_to_menus_table.php`

```php
Schema::table('menus', function (Blueprint $table) {
    $table->string('launcher_group', 50)->nullable()->after('sort_order');
});
```

#### Migration 2: create_launcher_groups_table

File: `src/database/migrations/2026_08_18_160000_create_launcher_groups_table.php`

```php
Schema::create('launcher_groups', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->string('label');
    $table->string('icon')->nullable();
    $table->unsignedInteger('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### Model: Menu.php

File: `src/app/Models/Menu.php`

Tambah `launcher_group` ke `$fillable` dan relasi:

```php
protected $fillable = [
    'parent_id',
    'system_route_id',
    'title',
    'icon',
    'sort_order',
    'is_sidebar',
    'launcher_group',
];

public function launcherGroup(): BelongsTo
{
    return $this->belongsTo(LauncherGroup::class, 'launcher_group', 'key');
}
```

#### Model: LauncherGroup.php

File: `src/app/Models/LauncherGroup.php`

```php
protected $fillable = [
    'key',
    'label',
    'icon',
    'sort_order',
    'is_active',
];

protected $casts = [
    'is_active' => 'boolean',
];

public function menus(): HasMany
{
    return $this->hasMany(Menu::class, 'launcher_group', 'key');
}
```

---

### M2 — Menu Management UI

#### Menu Create

File: `src/resources/views/pages/master/⚡menu-create/menu-create.php`

Tambah property dan rules:
```php
public ?string $launcher_group = null;

protected function rules(): array
{
    return [
        // ... rules lain
        'launcher_group' => ['nullable', 'string', 'max:50'],
    ];
}
```

File: `src/resources/views/pages/master/⚡menu-create/menu-create.blade.php`

Tambah dropdown:
```blade
<x-form.select label="Launcher Group" name="launcher_group" wire:model="launcher_group">
    <option value="">Tidak tampil di Launcher</option>
    @foreach($launcherGroups as $group)
        <option value="{{ $group->key }}">{{ $group->label }}</option>
    @endforeach
</x-form.select>
```

Update `render()` untuk pass `launcherGroups`:
```php
'launcherGroups' => \App\Models\LauncherGroup::where('is_active', true)->orderBy('sort_order')->get(),
```

#### Menu Edit

Sama seperti Menu Create, tapi tambah mount untuk load existing value:
```php
public function mount(Menu $menu): void
{
    // ... existing
    $this->launcher_group = $menu->launcher_group;
}
```

#### Menu List

File: `src/resources/views/pages/master/⚡menu-list/menu-list.blade.php`

Tambah kolom:
```blade
<th class="px-4 py-3 text-center text-sm font-semibold">Launcher Group</th>
```

Dan di row:
```blade
<td class="px-4 py-3 text-center">{{ $menu->launcherGroup?->label ?? '-' }}</td>
```

Update `menu-list.php` untuk eager load:
```php
->with(['parent', 'systemRoute', 'launcherGroup'])
```

#### MenuSeeder

File: `src/database/seeders/MenuSeeder.php`

Tambah parameter `launcherGroup` pada helper `createMenu()`:
```php
private function createMenu(
    string $title,
    ?int $routeId = null,
    ?int $parentId = null,
    ?string $icon = null,
    int $sortOrder = 0,
    bool $isSidebar = true,
    ?string $launcherGroup = null,
): Menu {
    return Menu::create([
        // ...
        'launcher_group' => $launcherGroup,
    ]);
}
```

---

### M3 — Launcher Component (Core)

#### Launcher PHP

File: `src/resources/views/components/⚡launcher/launcher.php`

```php
new class extends Component
{
    public array $groupOrder = ['transaksi', 'master_data', 'laporan', 'sistem'];

    public function render()
    {
        $activeGroups = LauncherGroup::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->pluck('key');

        $menus = Menu::query()
            ->with(['systemRoute'])
            ->whereNotNull('launcher_group')
            ->whereIn('launcher_group', $activeGroups)
            ->orderBy('sort_order')
            ->get();

        $filtered = $this->filterMenus($menus);
        $grouped = $filtered->groupBy('launcher_group');

        $ordered = collect();
        foreach ($this->groupOrder as $group) {
            if ($grouped->has($group)) {
                $ordered->put($group, $grouped->get($group));
            }
        }

        return $this->view(['groupedMenus' => $ordered]);
    }

    public function isActive(Menu $menu): bool
    {
        return optional($menu->systemRoute)->route_name === request()->route()?->getName();
    }

    protected function filterMenus(Collection $menus): Collection
    {
        $permissionName = app(PermissionNameService::class);

        return $menus->map(function (Menu $menu) use ($permissionName) {
            if (! $menu->systemRoute) {
                return $menu;
            }

            $permission = $permissionName->fromRoute(
                $menu->systemRoute->route_name
            );

            return auth()->user()?->can($permission)
                ? $menu
                : null;
        })->filter()->values();
    }
};
```

#### Launcher Blade

File: `src/resources/views/components/⚡launcher/launcher.blade.php`

```blade
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @foreach($groupedMenus as $group => $menus)
        @php
            $groupModel = \App\Models\LauncherGroup::where('key', $group)->first();
        @endphp
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="flex items-center gap-2 mb-3">
                @if($groupModel && $groupModel->icon)
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-blue-50 text-blue-600">
                        <i class="{{ $groupModel->icon }} text-xs"></i>
                    </span>
                @endif
                <h2 class="text-sm font-semibold text-gray-700">
                    {{ $groupModel?->label ?? ucfirst(str_replace('_', ' ', $group)) }}
                </h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($menus as $menu)
                    <a
                        href="{{ $menu->systemRoute?->route_name ? route($menu->systemRoute->route_name) : '#' }}"
                        wire:navigate
                        class="group flex flex-col items-center justify-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50/50 p-2.5 transition-all duration-200 hover:border-blue-400 hover:bg-white hover:shadow-sm"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-white text-blue-600 transition group-hover:bg-blue-50">
                            @if($menu->icon)
                                <i class="{{ $menu->icon }} text-sm"></i>
                            @else
                                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            @endif
                        </div>
                        <span class="text-[10px] font-medium text-center leading-tight text-gray-600 line-clamp-2">
                            {{ $menu->title }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
```

---

### M4 — Dashboard Integration

File: `src/resources/views/dashboard/index.blade.php`

```blade
@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            @livewire('components::launcher')
        </div>
    </div>
@endsection
```

Setelah login pengguna diarahkan ke dashboard, yang menampilkan Launcher.

---

### M5 — Launcher Group Manager

#### Route

File: `src/routes/web.php`

```php
Route::prefix('launcher-group')
    ->middleware(['auth', 'permission'])
    ->name('master.launcher-group.')
    ->group(function () {
        Route::livewire('/', 'pages::master.launcher-group-manager')
            ->name('list');
    });
```

#### Component PHP

File: `src/resources/views/pages/master/⚡launcher-group-manager/launcher-group-manager.php`

```php
new class extends Component
{
    public ?int $editingId = null;
    public string $key = '';
    public string $label = '';
    public ?string $icon = null;
    public int $sortOrder = 0;
    public bool $isActive = true;

    protected function rules(): array
    {
        $rules = [
            'label' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'isActive' => ['boolean'],
        ];

        if (! $this->editingId) {
            $rules['key'] = ['required', 'string', 'max:50', 'unique:launcher_groups,key'];
        }

        return $rules;
    }

    public function render()
    {
        return $this->view([
            'groups' => LauncherGroup::withCount('menus')->orderBy('sort_order')->get(),
        ])
        ->layout('layouts::app')
        ->title('Launcher Group Manager');
    }

    public function save(): void
    {
        $validated = $this->validate();

        $data = [
            'label' => $validated['label'],
            'icon' => $validated['icon'],
            'sort_order' => $validated['sortOrder'],
            'is_active' => $validated['isActive'],
        ];

        if ($this->editingId) {
            LauncherGroup::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Launcher group updated.');
        } else {
            $data['key'] = $validated['key'];
            LauncherGroup::create($data);
            session()->flash('success', 'Launcher group created.');
        }

        $this->resetForm();
    }

    public function edit(LauncherGroup $group): void
    {
        $this->editingId = $group->id;
        $this->label = $group->label;
        $this->icon = $group->icon;
        $this->sortOrder = $group->sort_order;
        $this->isActive = $group->is_active;
    }

    public function delete(LauncherGroup $group): void
    {
        if ($group->menus()->exists()) {
            session()->flash('error', 'Group tidak dapat dihapus karena masih digunakan oleh menu.');
            return;
        }

        $group->delete();
        session()->flash('success', 'Launcher group deleted.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->key = '';
        $this->label = '';
        $this->icon = null;
        $this->sortOrder = 0;
        $this->isActive = true;
    }
};
```

#### Component Blade

File: `src/resources/views/pages/master/⚡launcher-group-manager/launcher-group-manager.blade.php`

```blade
<x-form.card>
    <x-slot:title>
        <div class="flex flex-col">
            <span class="text-lg font-semibold">Launcher Group Manager</span>
            <span class="text-sm text-gray-500">Manage launcher categories for the dashboard</span>
        </div>
    </x-slot:title>

    @if (session('success'))
        <div class="mb-5 rounded-lg bg-green-100 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-5 rounded-lg bg-red-100 px-4 py-3 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-6">
        @if ($errors->any())
            <div class="rounded-lg bg-red-100 px-4 py-3 text-red-700">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Form --}}
            <div class="md:col-span-1 bg-white p-4 rounded-lg shadow-sm">
                <div class="grid grid-cols-1 gap-4">
                    @if(!$editingId)
                    <x-form.input label="Key" name="key" wire:model="key" />
                    @error('key')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 -mt-2">Unique identifier, lowercase underscore.</p>
                    @endif

                    <x-form.input label="Label" name="label" wire:model="label" />
                    @error('label')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror

                    <x-form.input label="Icon (Tabler)" name="icon" wire:model="icon" />
                    @error('icon')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 -mt-2">Example: ti ti-receipt</p>

                    <x-form.input label="Sort Order" name="sortOrder" type="number" wire:model="sortOrder" />
                    @error('sortOrder')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center gap-2">
                        <input id="is_active" type="checkbox" wire:model="isActive" class="rounded border-gray-300">
                        <label for="is_active">Active</label>
                    </div>
                    @error('isActive')
                        <p class="text-xs text-red-600 -mt-2">{{ $message }}</p>
                    @enderror

                    <div class="flex gap-2">
                        <button type="button" wire:click="save" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                            {{ $editingId ? 'Update' : 'Create' }}
                        </button>
                        @if($editingId)
                            <button type="button" wire:click="cancelEdit" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">
                                Cancel
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- List --}}
            <div class="md:col-span-2">
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Key</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Label</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Icon</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Sort</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Active</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Menus</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($groups as $group)
                                <tr>
                                    <td class="px-4 py-3">{{ $group->key }}</td>
                                    <td class="px-4 py-3">{{ $group->label }}</td>
                                    <td class="px-4 py-3">{{ $group->icon ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">{{ $group->sort_order }}</td>
                                    <td class="px-4 py-3 text-center">{{ $group->is_active ? 'Yes' : 'No' }}</td>
                                    <td class="px-4 py-3 text-center">{{ $group->menus_count }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-2">
                                            <button type="button" wire:click="edit({{ $group->id }})" class="rounded bg-yellow-500 px-3 py-1 text-sm text-white hover:bg-yellow-600">Edit</button>
                                            <button type="button" wire:click="delete({{ $group->id }})" wire:confirm="Yakin ingin menghapus group ini?" @if($group->menus_count > 0) disabled @endif class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700 @if($group->menus_count > 0) opacity-50 cursor-not-allowed @endif">{{ $group->menus_count > 0 ? 'Used' : 'Delete' }}</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada launcher group.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-form.card>
```

#### Seeder

File: `src/database/seeders/LauncherGroupSeeder.php`

```php
LauncherGroup::updateOrCreate(['key' => 'transaksi'], [
    'label' => 'Transaksi', 'icon' => 'ti ti-receipt', 'sort_order' => 1, 'is_active' => true
]);
// ... master_data, laporan, sistem
```

File: `src/database/seeders/DatabaseSeeder.php`

```php
$this->call([
    MenuSeeder::class,
    LauncherGroupSeeder::class,
    SuperAdminSeeder::class,
]);
```

---

### M6 — Config Export/Import

#### Export

File: `src/app/Console/Commands/FrameworkConfigExportCommand.php`

Tambah section `launcher_groups`:

```php
$launcherGroups = LauncherGroup::query()
    ->orderBy('sort_order')
    ->get()
    ->map(fn (LauncherGroup $group) => [
        'key' => $group->key,
        'label' => $group->label,
        'icon' => $group->icon,
        'sort_order' => $group->sort_order,
        'is_active' => $group->is_active,
    ])
    ->values()
    ->toArray();

$data = [
    // ...
    'launcher_groups' => $launcherGroups,
];
```

#### Import

File: `src/app/Console/Commands/FrameworkConfigImportCommand.php`

Tambah pass baru sebelum menu sync:

```php
$launcherGroups = $data['launcher_groups'] ?? [];

foreach ($launcherGroups as $groupData) {
    LauncherGroup::updateOrCreate(
        ['key' => $groupData['key']],
        [
            'label' => $groupData['label'],
            'icon' => $groupData['icon'] ?? null,
            'sort_order' => $groupData['sort_order'] ?? 0,
            'is_active' => $groupData['is_active'] ?? true,
        ]
    );
}
```

---

### M7 — Sidebar Fix (Important)

File: `src/resources/views/components/⚡sidebar/sidebar.blade.php`

Akar masalah: `wire:current` Livewire match by URL prefix, jadi `/system/roles` juga match `/system`.

Solusi: Gunakan Alpine.js exact path matching untuk child items.

```blade
<div x-data="{ currentPath: window.location.pathname, init() { document.addEventListener('livewire:navigated', () => { this.currentPath = window.location.pathname; }); } }">
    @foreach($menus as $menu)
        @if($menu->children->isNotEmpty())
            <div wire:click="toggleMenu({{ $menu->id }})">
                {{-- parent menu --}}
            </div>

            @if(in_array($menu->id, $openedMenus))
                @foreach($menu->children as $child)
                    <a
                        href="{{ route($child->systemRoute->route_name) }}"
                        wire:navigate
                        data-path="{{ parse_url(route($child->systemRoute->route_name), PHP_URL_PATH) }}"
                        x-bind:class="'ml-4 h-10 flex items-center rounded-lg px-3 transition-all duration-200 text-sm border-l-2 ' + ($el.dataset.path === window.location.pathname ? 'bg-amber-500/25 text-amber-300 font-semibold border-amber-400' : 'border-transparent hover:bg-white/10')"
                    >
                        {{ $child->title }}
                    </a>
                @endforeach
            @endif
        @endif
    @endforeach
</div>
```

---

## 31. Troubleshooting Launcher

### Field Key tidak muncul saat Edit

**Penyebab:** Field Key disembunyikan sepenuhnya saat edit untuk mencegah perubahan key yang bisa memutus relasi.

**Solusi:** Ini adalah expected behavior. Saat edit, key tidak perlu diubah.

### Sort Order selalu 0

**Penyebab:** Mismatch antara camelCase property (`sortOrder`) dan snake_case column (`sort_order`).

**Solusi:** Di `save()`, mapping manual:
```php
$data = ['sort_order' => $validated['sortOrder']];
```

### Sidebar highlight salah saat klik child route

**Penyebab:** `wire:current` match by URL prefix.

**Solusi:** Gunakan Alpine.js exact path matching seperti di bagian 30.

### Delete group gagal dengan error "masih digunakan"

**Penyebab:** Group masih memiliki menu yang menggunakannya.

**Solusi:** Hapus/ubah menu tersebut terlebih dahulu sebelum menghapus group.

---

## 32. Checklist Deploy Project Baru

1. Copy seluruh struktur folder
2. Setup `.env`
3. Setup database
4. Jalankan `php artisan migrate`
5. Jalankan `php artisan db:seed`
6. Jalankan `php artisan framework:route-sync`
7. Jalankan `php artisan framework:permission-sync`
8. Setup role & permission awal
9. Setup menu di `master.menu.list`
10. Setup launcher group di `master.launcher-group.list`
11. Assign menu ke launcher group
12. Test seluruh halaman

---

## 33. Catatan Penting untuk Project Baru

1. **Jangan hardcode menu** — selalu lewat database + route sync
2. **Jangan buat permission baru sembarang** — ikuti `module.resource.action`
3. **Gunakan `PermissionNameService`** sebagai pusat mapping
4. **Launcher adalah tambahan** — tidak mengubah sistem permission yang ada
5. **Export/import adalah single source of truth** untuk config
6. **Key launcher group tidak boleh diubah** setelah create — disembunyikan di form edit untuk keamanan
7. **Semua component pakai Livewire MFC pattern** (`new class extends Component`)
8. **Naming convention:**
   - Class: PascalCase
   - Method/property: camelCase
   - Database: snake_case
   - Route: dot.notation

---

## 34. Quick Reference

```bash
# Framework
php artisan framework:route-sync
php artisan framework:permission-sync
php artisan framework:config-export
php artisan framework:config-import
php artisan test

# Livewire
php artisan make:livewire pages::master.nama-component --mfc

# Database
php artisan migrate
php artisan db:seed
```

---

*Dokumentasi ini dibuat untuk jadi single source of truth yang mudah diikuti, dipelajari, dan dieksekusi oleh AI maupun manusia.*
