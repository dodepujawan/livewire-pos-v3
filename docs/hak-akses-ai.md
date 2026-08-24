📘 AI Development Rules (LOCK)
Permission Convention

Framework menggunakan dua jenis permission.

1. Page Permission (Otomatis)

Berasal dari Route.

Contoh:

master.barang.list
→ master.barang.view

master.barang.create
→ master.barang.create

master.barang.edit
→ master.barang.update

Developer tidak perlu menulis permission ini.

Permission dibuat otomatis oleh:

php artisan framework:permission-sync
2. Business Action Permission (Manual)

Untuk aksi yang bukan Page.

Contoh:

protected array $additionalPermissions = [
    'master.barang.delete',
    'master.barang.export',
    'master.barang.print',
    'master.barang.import',
];

Framework akan otomatis membuat permission tersebut saat menjalankan:

php artisan framework:permission-sync
AI Rules

Setiap kali AI membuat fitur baru, AI WAJIB mengikuti aturan berikut.

Rule 1

Jika membuat halaman:

List
Create
Edit
Show

AI tidak boleh membuat permission manual.

Karena sudah berasal dari Route.

Rule 2

Jika membuat function yang melakukan aksi bisnis, AI WAJIB menambahkan permission ke:

protected array $additionalPermissions = [];

Contoh:

protected array $additionalPermissions = [
    'master.customer.delete',
    'master.customer.export',
    'master.customer.import',
    'master.customer.print',
];
Rule 3

Sebelum membuat function baru, AI harus mengecek apakah permission sudah ada.

Jika belum, tambahkan ke:

protected array $additionalPermissions
Rule 4

Setiap function bisnis WAJIB melakukan authorization.

Misalnya:

public function delete(int $id)
{
    abort_unless(
        auth()->user()->can('master.barang.delete'),
        403
    );

    ...
}

atau nanti helper framework:

$this->authorizePermission(
    'master.barang.delete'
);
Rule 5

AI tidak boleh hardcode Role.

SALAH

if(auth()->user()->isAdmin())

SALAH

if(auth()->user()->role=="admin")

WAJIB

auth()->user()->can(...)
Rule 6

Setiap Button juga mengikuti permission.

Contoh

@if(auth()->user()->can('master.barang.delete'))

<x-button.delete />

@endif

atau nanti helper

@canPermission('master.barang.delete')

<x-button.delete />

@endCanPermission
Naming Convention

AI harus menggunakan format berikut.

Delete

resource.delete

Print

resource.print

Export

resource.export

Import

resource.import

Approve

resource.approve

Reject

resource.reject

Cancel

resource.cancel

Void

resource.void

Restore

resource.restore

Force Delete

resource.force-delete

Sync

resource.sync

Generate

resource.generate
AI Checklist

Setiap selesai membuat function, AI harus mengecek:

☐ Apakah function ini berasal dari Route?

YA

↓

Tidak perlu permission.

TIDAK

↓

Tambahkan ke:

protected array $additionalPermissions

↓

Gunakan

auth()->user()->can(...)

↓

Sembunyikan Button jika tidak memiliki permission.
