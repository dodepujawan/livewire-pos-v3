Setelah semua tab/sesi Kilo yang memakai project tersebut ditutup, hapus worktree menggunakan Git dari direktori project utama.

1. Pastikan worktree terdaftar
git worktree list
Cari baris:

/home/dode/ngoret/livewire_pos_v3/.kilo/worktrees/precious-puzzle
2. Cek apakah ada perubahan penting
git -C .kilo/worktrees/precious-puzzle status
Jika ada perubahan yang masih ingin disimpan, jangan hapus dulu. Salin atau commit perubahan tersebut terlebih dahulu.

3. Hapus worktree secara aman
git worktree remove .kilo/worktrees/precious-puzzle
Perintah ini menghapus checkout precious-puzzle sekaligus metadata Git worktree terkait. Project utama tidak terhapus.

4. Bersihkan metadata worktree yang sudah tidak ada
git worktree prune
Jika Git menolak karena ada perubahan
Jangan langsung menggunakan --force. Periksa dulu:

git -C .kilo/worktrees/precious-puzzle status --short
Jika sudah benar-benar yakin semua perubahan di worktree tidak diperlukan, gunakan:

git worktree remove --force .kilo/worktrees/precious-puzzle
git worktree prune
Hindari langsung menjalankan:

rm -rf .kilo/worktrees/precious-puzzle
karena itu dapat menghapus folder tetapi meninggalkan metadata Git worktree. git worktree remove adalah cara yang lebih aman.

Folder .kilo utama tidak perlu dihapus. Cukup hapus worktree yang tidak dipakai:

.kilo/worktrees/precious-puzzle/