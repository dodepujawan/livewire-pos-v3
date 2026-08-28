<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;


// ### CATATAN UBTUTK FRAMEWORK HAK AKSES LIVEWIRE ROUTE YANG MAU DI TAMPILKAN WAJIB ".LIST" ###

Route::get('/', function () {
    return redirect()->route('login');
});

// guest
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.process');

// logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
// dashboard (login wajib)
Route::get('/dashboard', function () {return view('dashboard.index');})->middleware('auth')->name('dashboard');

// Register
Route::prefix('auth')->middleware(['auth', 'permission'])->group(function () {
    Route::livewire('/register', 'pages::auth.register')->name('auth.register.create');
    Route::livewire('/register-list', 'pages::auth.register-list')->name('auth.register.list');
    Route::livewire('/register/{id}/edit', 'pages::auth.register-edit')->name('auth.register.edit');
    // Route Dibawah Udah Kadaluarsa
    Route::livewire('/permission-matrix', 'pages::auth.permission-matrix')->name('auth.permission.matrix');
});

// Barang
Route::prefix('master')->middleware(['auth', 'permission'])->group(function () {
    Route::livewire('/barang', 'pages::master.barang-list')->name('master.barang.list');
    Route::livewire('/barang/create', 'pages::master.barang-create')->name('master.barang.create');
    Route::livewire('/barang/{id}/edit', 'pages::master.barang-edit')->name('master.barang.edit');
});

// Cabang
Route::prefix('master')->middleware(['auth', 'permission'])->group(function () {
    Route::livewire('/cabang', 'pages::master.cabang-list')->name('master.cabang.list');
    Route::livewire('/cabang/create', 'pages::master.cabang-create')->name('master.cabang.create');
    Route::livewire('/cabang/{id}/edit', 'pages::master.cabang-edit')->name('master.cabang.edit');
});

// Pembelian
Route::prefix('transaksi/pembelian')->middleware(['auth', 'permission'])->name('transaksi.pembelian.')->group(function () {
    Route::livewire('/', 'pages::transaksi.pembelian-list')->name('list');
    Route::livewire('/create', 'pages::transaksi.pembelian-create')->name('create');
    Route::livewire('/{id}/edit', 'pages::transaksi.pembelian-edit')->name('edit');
});

// Piutang
Route::prefix('transaksi/piutang')->middleware(['auth', 'permission'])->name('transaksi.piutang.')->group(function () {
    Route::livewire('/', 'pages::transaksi.piutang-list')->name('list');
    Route::livewire('/create', 'pages::transaksi.piutang-create')->name('create');
    Route::livewire('/{id}/edit', 'pages::transaksi.piutang-edit')->name('edit');
});

// Hutang
Route::prefix('transaksi/hutang')->middleware(['auth', 'permission'])->name('transaksi.hutang.')->group(function () {
    Route::livewire('/', 'pages::transaksi.hutang-list')->name('list');
    Route::livewire('/create', 'pages::transaksi.hutang-create')->name('create');
    Route::livewire('/{id}/edit', 'pages::transaksi.hutang-edit')->name('edit');
});

// Pelunasan
Route::prefix('transaksi/pelunasan')->middleware(['auth', 'permission'])->name('transaksi.pelunasan.')->group(function () {
    Route::livewire('/create', 'pages::transaksi.pelunasan-create')->name('create');
});

//
Route::prefix('transaksi')->middleware(['auth', 'permission'])->group(function () {
    Route::livewire('/', 'pages::transaksi.transaksi-list')->name('transaksi.penjualan.list');
    Route::livewire('/create', 'pages::transaksi.transaksi-create')->name('transaksi.penjualan.create');
    Route::livewire('/{id}', 'pages::transaksi.transaksi-show')->name('transaksi.penjualan.show');
    Route::livewire('/{id}/edit', 'pages::transaksi.transaksi-edit')->name('transaksi.penjualan.edit');
});

Route::prefix('menu')->middleware(['auth', 'permission'])->group(function () {
    Route::livewire('/','pages::master.menu-list')->name('master.menu.list');
    Route::livewire('/create', 'pages::master.menu-create')->name('master.menu.create');
    Route::livewire('/{menu}/edit', 'pages::master.menu-edit')->name('master.menu.edit');
});

Route::prefix('system')->middleware(['auth', 'permission'])->name('system.')->group(function () {
    Route::livewire('/roles', 'pages::system.role-list')->name('role.list');
});

Route::prefix('laporan')->middleware(['auth', 'permission'])->group(function () {
    Route::livewire('/kas', 'pages::laporan.kas-list')->name('laporan.kas.list');
    Route::livewire('/buku-besar', 'pages::laporan.buku-besar-list')->name('laporan.buku-besar.list');
    Route::livewire('/laba-rugi', 'pages::laporan.laba-rugi-list')->name('laporan.laba-rugi.list');
    Route::livewire('/penjualan', 'pages::laporan.penjualan-list')->name('laporan.penjualan.list');
    Route::livewire('/stok', 'pages::laporan.stok-list')->name('laporan.stok.list');
    Route::livewire('/neraca', 'pages::laporan.neraca-list')->name('laporan.neraca.list');
    Route::livewire('/arus-kas', 'pages::laporan.arus-kas-list')->name('laporan.arus-kas.list');
});

// Hilangkan ini saat awal middleware(['auth', 'permission'])
Route::prefix('system')->middleware('auth')->name('system.')->group(function () {
    Route::livewire('/', 'pages::system.system-management')->name('list');
});

Route::prefix('launcher-group')->middleware(['auth', 'permission'])->name('master.launcher-group.')->group(function () {
    Route::livewire('/', 'pages::master.launcher-group-manager')->name('list');
});

// php artisan make:livewire pages::master.MenuEdit --mfc
// gpt-5.6-luna, gpt-5.6-terra, gpt-5.6-sol, gpt-5-mini, grok-4.3, DeepSeek-V4-Pro, DeepSeek-V4-Flash

// 1 Di Local
// php artisan framework:route-sync
// php artisan framework:permission-sync
// php artisan framework:config-export

// 2 Di VPS
// git pull
// php artisan framework:config-import

// memakai permisison scanner
// protected array $additionalPermissions = [
//     'system.role.delete',
//     'system.role.assign',
// ];
// php artisan make:model Permission -m

