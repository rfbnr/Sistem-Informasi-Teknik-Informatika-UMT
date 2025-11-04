<?php

namespace App\Http\Controllers\DigitalSignature;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HelpSupportController extends Controller
{
    /**
     * Tampilkan halaman Bantuan & Dukungan untuk Admin/Kaprodi
     */
    public function adminHelp()
    {
        $helpSections = $this->getAdminHelpSections();
        $faqItems = $this->getAdminFAQs();

        return view('digital-signature.admin.help-support', compact(
            'helpSections',
            'faqItems'
        ));
    }

    /**
     * Tampilkan halaman Bantuan & Dukungan untuk User/Mahasiswa
     */
    public function userHelp()
    {
        $helpSections = $this->getUserHelpSections();
        $faqItems = $this->getUserFAQs();

        return view('digital-signature.user.help-support', compact(
            'helpSections',
            'faqItems'
        ));
    }

    /**
     * Dapatkan bagian bantuan Admin/Kaprodi dengan dokumentasi lengkap
     */
    private function getAdminHelpSections()
    {
        return [
            [
                'id' => 'dashboard',
                'icon' => 'tachometer-alt',
                'color' => 'primary',
                'title' => 'Ikhtisar Dashboard',
                'description' => 'Memahami dashboard dan statistik tanda tangan digital Anda',
                'items' => [
                    [
                        'title' => 'Kartu Statistik',
                        'content' => 'Dashboard menampilkan statistik real-time meliputi:<br>
                            • <strong>Total Tanda Tangan</strong>: Jumlah tanda tangan digital yang dihasilkan<br>
                            • <strong>Persetujuan Tertunda</strong>: Dokumen menunggu persetujuan Anda (klik untuk navigasi)<br>
                            • <strong>Tanda Tangan Terverifikasi</strong>: Dokumen yang berhasil diverifikasi<br>
                            • <strong>Dokumen Ditolak</strong>: Dokumen yang ditolak'
                    ],
                    [
                        'title' => 'Aktivitas Terbaru',
                        'content' => 'Pantau tindakan terbaru dalam sistem termasuk pengajuan baru, persetujuan, dan penolakan. Setiap aktivitas menampilkan timestamp dan informasi pengguna.'
                    ],
                    [
                        'title' => 'Aksi Cepat',
                        'content' => 'Akses fungsi yang sering digunakan langsung dari dashboard:<br>
                            • Lihat permintaan persetujuan tertunda<br>
                            • Akses tanda tangan dokumen<br>
                            • Buat laporan'
                    ]
                ]
            ],
            [
                'id' => 'approval-requests',
                'icon' => 'clipboard-check',
                'color' => 'warning',
                'title' => 'Mengelola Permintaan Persetujuan',
                'description' => 'Cara meninjau dan memproses permintaan persetujuan dokumen',
                'items' => [
                    [
                        'title' => 'Melihat Permintaan',
                        'content' => 'Navigasi ke <strong>Permintaan Persetujuan</strong> dari sidebar. Anda akan melihat daftar semua permintaan dengan filter untuk:<br>
                            • Status (Tertunda, Disetujui, Ditolak, Ditandatangani)<br>
                            • Rentang tanggal<br>
                            • Nama pengguna/mahasiswa'
                    ],
                    [
                        'title' => 'Menyetujui Dokumen',
                        'content' => 'Untuk menyetujui permintaan:<br>
                            1. Klik permintaan untuk melihat detail<br>
                            2. Tinjau pratinjau dokumen dengan cermat<br>
                            3. Periksa metadata dokumen dan informasi pengguna<br>
                            4. Klik tombol <strong>Setujui</strong><br>
                            5. Tambahkan catatan persetujuan opsional<br>
                            6. Konfirmasi persetujuan<br><br>
                            Setelah disetujui, pengguna dapat melanjutkan untuk menandatangani dokumen secara digital.'
                    ],
                    [
                        'title' => 'Menolak Dokumen',
                        'content' => 'Untuk menolak permintaan:<br>
                            1. Klik permintaan untuk melihat detail<br>
                            2. Klik tombol <strong>Tolak</strong><br>
                            3. Berikan alasan penolakan yang jelas (wajib)<br>
                            4. Konfirmasi penolakan<br><br>
                            Pengguna akan diberitahu melalui email tentang penolakan dan alasannya.'
                    ],
                    [
                        'title' => 'Pratinjau Dokumen',
                        'content' => 'Sebelum membuat keputusan, Anda dapat:<br>
                            • Pratinjau PDF langsung di browser<br>
                            • Unduh dokumen asli<br>
                            • Periksa metadata dokumen (ukuran file, tanggal unggah, dll.)<br>
                            • Lihat catatan pengajuan pengguna'
                    ]
                ]
            ],
            [
                'id' => 'document-signatures',
                'icon' => 'file-signature',
                'color' => 'success',
                'title' => 'Manajemen Tanda Tangan Dokumen',
                'description' => 'Pantau dan kelola dokumen yang ditandatangani',
                'items' => [
                    [
                        'title' => 'Melihat Dokumen Tertandatangan',
                        'content' => 'Akses semua dokumen tertandatangan dari menu <strong>Tanda Tangan Dokumen</strong>. Setiap entri menampilkan:<br>
                            • Status tanda tangan (Tertunda, Ditandatangani, Terverifikasi, Tidak Valid)<br>
                            • Nama dan jenis dokumen<br>
                            • ID tanda tangan digital<br>
                            • Status verifikasi QR code<br>
                            • Tanggal penandatanganan dan pengguna'
                    ],
                    [
                        'title' => 'Status Dokumen',
                        'content' => '<strong>Tertunda</strong>: Dokumen disetujui tetapi belum ditandatangani oleh pengguna<br>
                            <strong>Ditandatangani</strong>: Pengguna telah menyelesaikan penandatanganan digital<br>
                            <strong>Terverifikasi</strong>: Tanda tangan telah diverifikasi otomatis oleh sistem<br>
                            <strong>Tidak Valid</strong>: Tanda tangan dibatalkan oleh admin (dokumen dirusak atau masalah lain)'
                    ],
                    [
                        'title' => 'Mengunduh & Melihat',
                        'content' => 'Untuk setiap dokumen tertandatangan, Anda dapat:<br>
                            • <strong>Lihat</strong>: Pratinjau PDF tertandatangan dengan QR code tertanam<br>
                            • <strong>Unduh</strong>: Dapatkan file PDF tertandatangan final<br>
                            • <strong>Unduh QR Code</strong>: Dapatkan gambar QR code standalone<br>
                            • <strong>Lihat Detail</strong>: Lihat metadata tanda tangan lengkap'
                    ],
                    [
                        'title' => 'Membatalkan Tanda Tangan',
                        'content' => 'Jika Anda menemukan dokumen dirusak atau perlu mencabut tanda tangan:<br>
                            1. Klik dokumen<br>
                            2. Klik tombol <strong>Batalkan</strong><br>
                            3. Berikan alasan pembatalan<br>
                            4. Konfirmasi tindakan<br><br>
                            <span class="text-danger"><strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan!</span>'
                    ]
                ]
            ],
            [
                'id' => 'reports',
                'icon' => 'chart-bar',
                'color' => 'info',
                'title' => 'Laporan & Analitik',
                'description' => 'Hasilkan dan ekspor laporan komprehensif',
                'items' => [
                    [
                        'title' => 'Dashboard Laporan Utama',
                        'content' => 'Akses analitik detail yang menampilkan:<br>
                            • Total permintaan persetujuan dan tingkat penyelesaian<br>
                            • Statistik tanda tangan dokumen<br>
                            • Analitik QR code dan jumlah pemindaian<br>
                            • Waktu pemrosesan rata-rata<br>
                            • Tingkat penolakan dan alasannya<br>
                            • Grafik timeline untuk tren'
                    ],
                    [
                        'title' => 'Analitik QR Code',
                        'content' => 'Laporan QR code khusus menampilkan:<br>
                            • Total QR code yang dihasilkan<br>
                            • Total pemindaian dan rata-rata pemindaian per code<br>
                            • QR code paling banyak dipindai (top 5)<br>
                            • Code yang tidak pernah dipindai<br>
                            • Code aktif vs kadaluwarsa<br>
                            • Informasi akses terakhir dengan alamat IP'
                    ],
                    [
                        'title' => 'Metrik Kinerja',
                        'content' => 'Analisis kinerja sistem:<br>
                            • <strong>Kecepatan Persetujuan</strong>: Waktu persetujuan rata-rata, tercepat, terlambat, median<br>
                            • <strong>Tingkat Tanda Tangan</strong>: Persentase dokumen yang disetujui dan ditandatangani<br>
                            • <strong>Tren Penyelesaian</strong>: Perbandingan bulan ke bulan<br>
                            • <strong>Benchmark</strong>: Bandingkan dengan metrik target'
                    ],
                    [
                        'title' => 'Mengekspor Laporan',
                        'content' => 'Ekspor data dalam berbagai format:<br>
                            • <strong>Ekspor CSV</strong>: Data tabular detail untuk analisis Excel<br>
                            • <strong>Ekspor PDF</strong>: Laporan profesional dengan grafik<br><br>
                            Kedua ekspor mencakup:<br>
                            • Data permintaan persetujuan lengkap<br>
                            • Distribusi status<br>
                            • Statistik waktu pemrosesan<br>
                            • Pilihan rentang tanggal kustom'
                    ]
                ]
            ],
            [
                'id' => 'activity-logs',
                'icon' => 'history',
                'color' => 'secondary',
                'title' => 'Log Aktivitas & Jejak Audit',
                'description' => 'Pantau semua aktivitas sistem untuk keamanan dan kepatuhan',
                'items' => [
                    [
                        'title' => 'Log Audit',
                        'content' => 'Jejak audit lengkap dari semua tindakan:<br>
                            • Pengajuan dan pengeditan pengguna<br>
                            • Persetujuan dan penolakan admin<br>
                            • Event pembuatan tanda tangan<br>
                            • Pembatalan dokumen<br>
                            • Tindakan tingkat sistem<br><br>
                            Setiap entri log mencakup:<br>
                            • Timestamp<br>
                            • Pengguna/Admin yang melakukan tindakan<br>
                            • Alamat IP dan user agent<br>
                            • Status sebelum/sesudah<br>
                            • Metadata detail'
                    ],
                    [
                        'title' => 'Log Verifikasi',
                        'content' => 'Lacak semua upaya verifikasi tanda tangan:<br>
                            • Pemindaian QR code publik<br>
                            • Upaya verifikasi manual<br>
                            • Status sukses/gagal<br>
                            • Alamat IP pengunjung<br>
                            • Timestamp akses'
                    ],
                    [
                        'title' => 'Memfilter Log',
                        'content' => 'Gunakan filter lanjutan untuk menemukan event spesifik:<br>
                            • Pilihan rentang tanggal<br>
                            • Filter jenis tindakan<br>
                            • Filter pengguna/admin<br>
                            • Filter status<br>
                            • Cari berdasarkan nama dokumen atau ID'
                    ],
                    [
                        'title' => 'Mengekspor Log',
                        'content' => 'Ekspor log untuk kepatuhan atau investigasi:<br>
                            • Format CSV untuk analisis detail<br>
                            • Mencakup semua field metadata<br>
                            • Data yang difilter berdasarkan pilihan Anda'
                    ]
                ]
            ],
            [
                'id' => 'security',
                'icon' => 'shield-alt',
                'color' => 'danger',
                'title' => 'Keamanan & Praktik Terbaik',
                'description' => 'Pastikan keamanan sistem dan integritas data',
                'items' => [
                    [
                        'title' => 'Keamanan Tanda Tangan Digital',
                        'content' => 'Sistem kami menggunakan:<br>
                            • <strong>RSA-2048</strong> algoritma enkripsi<br>
                            • <strong>SHA-256</strong> hashing untuk integritas dokumen<br>
                            • <strong>Pasangan kunci unik</strong> untuk setiap tanda tangan<br>
                            • <strong>Penyimpanan kunci terenkripsi</strong> di database<br>
                            • <strong>Verifikasi hash dokumen</strong> sebelum penandatanganan'
                    ],
                    [
                        'title' => 'Verifikasi QR Code',
                        'content' => 'QR code menyediakan:<br>
                            • Kode verifikasi pendek (12 karakter)<br>
                            • Pemetaan payload terenkripsi<br>
                            • Tanggal kadaluwarsa (default: 5 tahun)<br>
                            • Pelacakan akses (IP, timestamp, jumlah)<br>
                            • Pembatasan kecepatan (10 upaya per jam)'
                    ],
                    [
                        'title' => 'Integritas Dokumen',
                        'content' => 'Sistem secara otomatis:<br>
                            • Menghasilkan hash SHA-256 saat unggah dokumen<br>
                            • Memverifikasi hash sebelum penandatanganan (deteksi perusakan)<br>
                            • Menolak tanda tangan jika dokumen dimodifikasi<br>
                            • Mencatat semua kegagalan pemeriksaan integritas'
                    ],
                    [
                        'title' => 'Praktik Terbaik',
                        'content' => '<strong>LAKUKAN:</strong><br>
                            • Tinjau dokumen dengan cermat sebelum persetujuan<br>
                            • Berikan alasan penolakan yang jelas<br>
                            • Periksa log aktivitas secara berkala untuk aktivitas mencurigakan<br>
                            • Pantau tanda tangan yang akan kadaluwarsa<br><br>
                            <strong>JANGAN:</strong><br>
                            • Menyetujui dokumen tanpa peninjauan menyeluruh<br>
                            • Berbagi kredensial admin<br>
                            • Membatalkan tanda tangan tanpa alasan valid<br>
                            • Mengabaikan peringatan atau peringatan keamanan'
                    ]
                ]
            ]
        ];
    }

    /**
     * Dapatkan bagian bantuan User/Mahasiswa
     */
    private function getUserHelpSections()
    {
        return [
            [
                'id' => 'getting-started',
                'icon' => 'play-circle',
                'color' => 'primary',
                'title' => 'Memulai',
                'description' => 'Panduan cepat untuk sistem tanda tangan digital',
                'items' => [
                    [
                        'title' => 'Ikhtisar Sistem',
                        'content' => 'Sistem Tanda Tangan Digital memungkinkan Anda untuk:<br>
                            • Mengajukan dokumen untuk persetujuan<br>
                            • Menandatangani dokumen secara digital dengan QR code<br>
                            • Melacak status pengajuan Anda<br>
                            • Mengunduh dokumen tertandatangan<br><br>
                            <strong>Alur Proses:</strong><br>
                            1. Unggah Dokumen → 2. Persetujuan Kaprodi → 3. Tanda Tangan Digital → 4. Selesai'
                    ],
                    [
                        'title' => 'Persyaratan Akun',
                        'content' => 'Untuk menggunakan sistem, Anda memerlukan:<br>
                            • Akun mahasiswa yang valid (NIM)<br>
                            • Alamat email terdaftar<br>
                            • Status mahasiswa aktif<br><br>
                            Hubungi admin jika Anda memiliki masalah akun.'
                    ]
                ]
            ],
            [
                'id' => 'submit-document',
                'icon' => 'upload',
                'color' => 'success',
                'title' => 'Mengajukan Dokumen',
                'description' => 'Cara mengunggah dan mengajukan dokumen untuk persetujuan',
                'items' => [
                    [
                        'title' => 'Persyaratan Dokumen',
                        'content' => 'Pastikan dokumen Anda memenuhi persyaratan berikut:<br>
                            • <strong>Format</strong>: Hanya PDF<br>
                            • <strong>Ukuran File</strong>: Maksimal 25 MB<br>
                            • <strong>Konten</strong>: Teks yang jelas dan dapat dibaca<br>
                            • <strong>Halaman</strong>: Dokumen lengkap (tidak ada halaman yang hilang)<br>
                            • <strong>Kualitas</strong>: Bukan hasil scan atau gambar berkualitas rendah'
                    ],
                    [
                        'title' => 'Proses Unggah',
                        'content' => 'Untuk mengajukan dokumen:<br>
                            1. Buka menu <strong>Permintaan Persetujuan</strong><br>
                            2. Klik tombol <strong>Unggah Dokumen</strong><br>
                            3. Isi formulir:<br>
                            &nbsp;&nbsp;&nbsp;• Nama Dokumen (deskriptif)<br>
                            &nbsp;&nbsp;&nbsp;• Jenis Dokumen (pilih dari dropdown)<br>
                            &nbsp;&nbsp;&nbsp;• Catatan (jelaskan tujuan)<br>
                            4. Pilih file PDF dari komputer Anda<br>
                            5. Tinjau semua informasi<br>
                            6. Klik <strong>Ajukan untuk Persetujuan</strong><br><br>
                            Anda akan menerima notifikasi email setelah diajukan.'
                    ],
                    [
                        'title' => 'Setelah Pengajuan',
                        'content' => 'Yang terjadi selanjutnya:<br>
                            • Status berubah menjadi <span class="badge bg-warning">Tertunda</span><br>
                            • Admin/Kaprodi menerima notifikasi<br>
                            • Anda dapat melacak status di <strong>Dokumen Saya</strong><br>
                            • Anda akan diberitahu melalui email tentang persetujuan/penolakan<br><br>
                            <strong>Waktu persetujuan rata-rata:</strong> 1-3 hari kerja'
                    ]
                ]
            ],
            [
                'id' => 'signing-process',
                'icon' => 'pen-fancy',
                'color' => 'info',
                'title' => 'Proses Tanda Tangan Digital',
                'description' => 'Cara menandatangani dokumen yang disetujui',
                'items' => [
                    [
                        'title' => 'Kapan Menandatangani',
                        'content' => 'Anda dapat menandatangani setelah:<br>
                            • Status dokumen berubah menjadi <span class="badge bg-info">Disetujui - Siap Ditandatangani</span><br>
                            • Anda menerima notifikasi email persetujuan<br>
                            • Tombol "Tanda Tangani Dokumen" muncul di Dokumen Saya'
                    ],
                    [
                        'title' => 'Antarmuka Penandatanganan',
                        'content' => 'Halaman penandatanganan memiliki dua bagian utama:<br>
                            <strong>Panel Kiri:</strong> Template QR Code<br>
                            • Seret QR code<br>
                            • Pilih ukuran (Kecil/Sedang/Besar)<br>
                            • Tombol penempatan otomatis tersedia<br><br>
                            <strong>Panel Kanan:</strong> Pratinjau PDF<br>
                            • Lihat dokumen Anda secara real-time<br>
                            • Seret QR code ke posisi yang diinginkan<br>
                            • Zoom in/out sesuai kebutuhan'
                    ],
                    [
                        'title' => 'Langkah-Langkah Penandatanganan',
                        'content' => '1. <strong>Posisikan QR Code:</strong><br>
                            &nbsp;&nbsp;&nbsp;• Seret QR dari kiri ke pratinjau PDF<br>
                            &nbsp;&nbsp;&nbsp;• Atau klik "Penempatan Otomatis" untuk posisi otomatis<br>
                            &nbsp;&nbsp;&nbsp;• Sesuaikan ukuran jika diperlukan<br>
                            &nbsp;&nbsp;&nbsp;• Pindahkan ke lokasi yang disukai (biasanya pojok kanan bawah)<br><br>
                            2. <strong>Pratinjau:</strong><br>
                            &nbsp;&nbsp;&nbsp;• Klik "Pratinjau Dokumen" untuk melihat hasil akhir<br>
                            &nbsp;&nbsp;&nbsp;• Periksa visibilitas dan posisi QR code<br><br>
                            3. <strong>Tanda Tangani:</strong><br>
                            &nbsp;&nbsp;&nbsp;• Klik tombol "Tanda Tangani Dokumen"<br>
                            &nbsp;&nbsp;&nbsp;• Konfirmasi tindakan Anda<br>
                            &nbsp;&nbsp;&nbsp;• Tunggu pemrosesan (biasanya 5-10 detik)<br>
                            &nbsp;&nbsp;&nbsp;• Unduh dokumen tertandatangan Anda!'
                    ],
                    [
                        'title' => 'Penandatanganan Mobile',
                        'content' => 'Sistem sepenuhnya responsif untuk perangkat mobile:<br>
                            • Tata letak vertikal (QR atas, PDF bawah)<br>
                            • Drag & drop ramah sentuh<br>
                            • Kontrol tetap di bagian bawah layar<br>
                            • Ketuk dan seret QR code untuk memposisikan<br>
                            • Fungsi pratinjau dan tanda tangan yang sama'
                    ],
                    [
                        'title' => 'Catatan Penting',
                        'content' => '<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> <strong>Penting:</strong></span><br>
                            • QR code HARUS terlihat di dokumen akhir<br>
                            • Jangan tutup browser selama proses penandatanganan<br>
                            • Tanda tangan diverifikasi otomatis oleh sistem<br>
                            • Anda hanya dapat menandatangani sekali (tidak ada tanda tangan ulang)<br>
                            • Dokumen tertandatangan tidak dapat diedit'
                    ]
                ]
            ],
            [
                'id' => 'my-documents',
                'icon' => 'folder-open',
                'color' => 'warning',
                'title' => 'Dokumen Saya & Status',
                'description' => 'Lacak dan kelola dokumen yang diajukan',
                'items' => [
                    [
                        'title' => 'Status Dokumen',
                        'content' => 'Memahami badge status:<br>
                            • <span class="badge bg-warning">Tertunda</span>: Menunggu persetujuan admin<br>
                            • <span class="badge bg-info">Disetujui</span>: Siap untuk tanda tangan Anda<br>
                            • <span class="badge bg-primary">Ditandatangani Pengguna</span>: Anda menandatangani, menunggu verifikasi<br>
                            • <span class="badge bg-success">Selesai</span>: Sepenuhnya ditandatangani dan diverifikasi<br>
                            • <span class="badge bg-danger">Ditolak</span>: Admin menolak pengajuan Anda'
                    ],
                    [
                        'title' => 'Tindakan yang Tersedia',
                        'content' => 'Tergantung pada status, Anda dapat:<br>
                            • <strong>Lihat</strong>: Lihat detail dokumen<br>
                            • <strong>Tanda Tangani</strong>: Tanda tangani dokumen yang disetujui secara digital<br>
                            • <strong>Unduh Asli</strong>: Dapatkan file yang diunggah<br>
                            • <strong>Unduh Tertandatangan</strong>: Dapatkan PDF tertandatangan final<br>
                            • <strong>Unduh QR Code</strong>: Dapatkan gambar QR verifikasi<br>
                            • <strong>Lihat Alasan Penolakan</strong>: Lihat mengapa dokumen ditolak'
                    ],
                    // [
                    //     'title' => 'Mengajukan Ulang Dokumen yang Ditolak',
                    //     'content' => 'Jika dokumen Anda ditolak:<br>
                    //         1. Baca alasan penolakan dengan cermat<br>
                    //         2. Perbaiki masalah yang disebutkan<br>
                    //         3. Siapkan dokumen yang diperbaiki<br>
                    //         4. Ajukan sebagai permintaan baru<br>
                    //         5. Referensi pengajuan sebelumnya di catatan (opsional)'
                    // ]
                ]
            ],
            [
                'id' => 'my-signatures',
                'icon' => 'file-signature',
                'color' => 'success',
                'title' => 'Dokumen Tertandatangan Saya',
                'description' => 'Akses dan kelola dokumen yang ditandatangani secara digital',
                'items' => [
                    [
                        'title' => 'Melihat Dokumen Tertandatangan',
                        'content' => 'Akses dari menu <strong>Tanda Tangan Saya</strong>:<br>
                            • Lihat semua tanda tangan lengkap Anda<br>
                            • Lihat detail dan metadata tanda tangan<br>
                            • Periksa ID tanda tangan digital<br>
                            • Lihat tanggal dan waktu penandatanganan<br>
                            • Lihat informasi QR code'
                    ],
                    [
                        'title' => 'Mengunduh File',
                        'content' => 'Untuk setiap dokumen tertandatangan:<br>
                            • <strong>PDF Tertandatangan</strong>: Dokumen lengkap dengan QR code tertanam<br>
                            • <strong>Gambar QR Code</strong>: QR verifikasi standalone<br>
                            • <strong>URL Verifikasi</strong>: Link untuk verifikasi publik<br><br>
                            Semua unduhan tidak terbatas dan permanen.'
                    ],
                    [
                        'title' => 'Membagikan Dokumen Tertandatangan',
                        'content' => 'Dokumen tertandatangan Anda dapat:<br>
                            • Dibagikan melalui email atau pesan<br>
                            • Diunggah ke portal akademik<br>
                            • Dicetak (QR code tetap dapat dipindai)<br>
                            • Diverifikasi secara publik menggunakan QR code<br><br>
                            Siapa pun dapat memverifikasi keaslian dengan memindai QR code.'
                    ]
                ]
            ],
            [
                'id' => 'verification',
                'icon' => 'qrcode',
                'color' => 'secondary',
                'title' => 'Verifikasi QR Code',
                'description' => 'Memahami cara kerja verifikasi',
                'items' => [
                    [
                        'title' => 'Apa itu QR Code?',
                        'content' => 'QR code yang tertanam di dokumen tertandatangan Anda:<br>
                            • Berisi data verifikasi terenkripsi<br>
                            • Link ke halaman validasi tanda tangan<br>
                            • Tidak dapat disalin atau dipalsukan<br>
                            • Valid selama 3 tahun sejak pembuatan<br>
                            • Melacak upaya verifikasi'
                    ],
                    [
                        'title' => 'Cara Verifikasi',
                        'content' => 'Siapa pun dapat memverifikasi dokumen Anda:<br>
                            1. <strong>Pindai QR Code:</strong> Gunakan kamera ponsel atau aplikasi pemindai QR<br>
                            2. <strong>Redirect Otomatis:</strong> Membuka halaman verifikasi<br>
                            3. <strong>Lihat Detail:</strong> Menampilkan:<br>
                            &nbsp;&nbsp;&nbsp;• Nama dokumen<br>
                            &nbsp;&nbsp;&nbsp;• Status tanda tangan (Valid/Tidak Valid)<br>
                            &nbsp;&nbsp;&nbsp;• Tanggal penandatanganan dan pengguna<br>
                            &nbsp;&nbsp;&nbsp;• ID tanda tangan digital<br>
                            &nbsp;&nbsp;&nbsp;• Detail enkripsi<br>
                            4. <strong>Konfirmasi:</strong> Badge hijau = Valid, Merah = Tidak Valid'
                    ],
                    [
                        'title' => 'Halaman Verifikasi Publik',
                        'content' => 'Metode verifikasi alternatif:<br>
                            • Kunjungi halaman verifikasi publik<br>
                            • Masukkan URL verifikasi secara manual<br>
                            • Masukkan kode pendek secara manual (12 karakter)<br>
                            • Lihat riwayat verifikasi (hanya dokumen Anda)'
                    ]
                ]
            ],
            [
                'id' => 'troubleshooting',
                'icon' => 'tools',
                'color' => 'danger',
                'title' => 'Pemecahan Masalah & FAQ',
                'description' => 'Masalah umum dan solusi',
                'items' => [
                    [
                        'title' => 'Kesalahan Unggah',
                        'content' => '<strong>Masalah:</strong> Error "File terlalu besar"<br>
                            <strong>Solusi:</strong> Kompres PDF atau kurangi kualitas (max 25 MB)<br><br>
                            <strong>Masalah:</strong> "Jenis file tidak valid"<br>
                            <strong>Solusi:</strong> Pastikan file dalam format PDF (ekstensi .pdf)<br><br>
                            <strong>Masalah:</strong> "Unggah gagal"<br>
                            <strong>Solusi:</strong> Periksa koneksi internet, coba lagi, atau gunakan browser berbeda'
                    ],
                    [
                        'title' => 'Masalah Penandatanganan',
                        'content' => '<strong>Masalah:</strong> Tidak dapat menyeret QR code<br>
                            <strong>Solusi:</strong> Coba browser berbeda (Chrome, Firefox direkomendasikan)<br><br>
                            <strong>Masalah:</strong> Tombol "Tanda Tangani Dokumen" dinonaktifkan<br>
                            <strong>Solusi:</strong> Pastikan QR code sudah ditempatkan di PDF terlebih dahulu<br><br>
                            <strong>Masalah:</strong> Penandatanganan memakan waktu terlalu lama<br>
                            <strong>Solusi:</strong> Tunggu dengan sabar (dapat memakan waktu hingga 30 detik), jangan refresh'
                    ],
                    [
                        'title' => 'Masalah Unduhan',
                        'content' => '<strong>Masalah:</strong> Tidak dapat mengunduh PDF tertandatangan<br>
                            <strong>Solusi:</strong> Periksa pengaturan pop-up blocker browser<br><br>
                            <strong>Masalah:</strong> File yang diunduh rusak<br>
                            <strong>Solusi:</strong> Hapus cache browser dan unduh lagi<br><br>
                            <strong>Masalah:</strong> Gambar QR code tidak mau diunduh<br>
                            <strong>Solusi:</strong> Klik kanan dan "Simpan Gambar Sebagai"'
                    ],
                    [
                        'title' => 'Masalah Umum',
                        'content' => 'Jika masalah berlanjut:<br>
                            • Hapus cache dan cookie browser<br>
                            • Coba mode incognito/private browsing<br>
                            • Gunakan versi browser yang diperbarui<br>
                            • Hubungi dukungan IT dengan pesan error<br>
                            • Email: support@umt.ac.id<br>
                            • Sertakan NIM dan deskripsi Anda'
                    ]
                ]
            ]
        ];
    }

    /**
     * Dapatkan FAQ Admin
     */
    private function getAdminFAQs()
    {
        return [
            [
                'question' => 'Bagaimana cara menyetujui beberapa dokumen sekaligus?',
                'answer' => 'Saat ini, sistem memerlukan peninjauan individual untuk setiap dokumen untuk memastikan kualitas. Persetujuan massal tidak tersedia untuk mempertahankan standar verifikasi dokumen.'
            ],
            [
                'question' => 'Bisakah saya mencabut persetujuan setelah diberikan?',
                'answer' => 'Tidak, setelah dokumen disetujui, tidak dapat dicabut secara langsung. Jika mahasiswa belum menandatangani, Anda dapat menghubungi administrator sistem. Jika sudah ditandatangani, Anda harus menggunakan fungsi "Batalkan Tanda Tangan" dengan alasan yang jelas.'
            ],
            [
                'question' => 'Apa yang terjadi jika mahasiswa tidak menandatangani setelah persetujuan?',
                'answer' => 'Dokumen yang disetujui tetap dalam status "Disetujui - Siap Ditandatangani" tanpa batas waktu. Tidak ada kadaluwarsa otomatis. Anda dapat menindaklanjuti dengan mahasiswa atau menghubungi mereka secara langsung.'
            ],
            [
                'question' => 'Berapa lama tanda tangan digital berlaku?',
                'answer' => 'Tanda tangan digital valid selama 5 tahun sejak tanggal penandatanganan secara default. Verifikasi QR code akan berfungsi selama periode ini. Setelah kadaluwarsa, tanda tangan tetap valid tetapi verifikasi dapat menampilkan sebagai "kadaluwarsa".'
            ],
            [
                'question' => 'Bisakah saya mengedit dokumen yang sudah ditandatangani?',
                'answer' => 'Tidak, dokumen tertandatangan tidak dapat diedit. Modifikasi apa pun akan membatalkan tanda tangan dan pemeriksaan hash dokumen akan gagal. Jika perubahan diperlukan, mahasiswa harus mengajukan dokumen baru.'
            ],
            [
                'question' => 'Apa perbedaan antara status "Ditandatangani" dan "Terverifikasi"?',
                'answer' => 'Dalam sistem saat ini, tanda tangan diverifikasi otomatis setelah penandatanganan. Kedua status secara efektif berarti sama - dokumen berhasil ditandatangani dengan tanda tangan digital yang valid. "Terverifikasi" adalah status akhir.'
            ],
            [
                'question' => 'Bagaimana cara mengekspor semua data persetujuan untuk periode tertentu?',
                'answer' => 'Buka Laporan & Analitik → Klik tombol "Ekspor Laporan" → Pilih rentang tanggal → Pilih format CSV atau PDF → Unduh. Ekspor mencakup semua permintaan persetujuan dengan metadata lengkap.'
            ],
            [
                'question' => 'Mengapa ada dua opsi "ditolak" dalam statistik?',
                'answer' => 'Hanya ada satu titik penolakan: ketika Anda menolak permintaan persetujuan. Status "Tidak Valid" adalah untuk dokumen yang sudah ditandatangani yang Anda batalkan secara manual kemudian. Mereka dilacak secara terpisah untuk kejelasan.'
            ]
        ];
    }

    /**
     * Dapatkan FAQ User
     */
    private function getUserFAQs()
    {
        return [
            [
                'question' => 'Berapa lama waktu yang diperlukan untuk mendapatkan persetujuan?',
                'answer' => 'Waktu persetujuan rata-rata adalah 1-3 hari kerja. Anda akan menerima notifikasi email setelah dokumen Anda ditinjau. Periksa status pengajuan Anda secara berkala di "Dokumen Saya".'
            ],
            [
                'question' => 'Bisakah saya mengedit dokumen saya setelah pengajuan?',
                'answer' => 'Tidak, dokumen yang diajukan tidak dapat diedit. Jika Anda perlu membuat perubahan, Anda harus mengajukan permintaan baru. Jika dokumen Anda masih tertunda, Anda dapat menghubungi admin untuk menolaknya terlebih dahulu.'
            ],
            [
                'question' => 'Mengapa dokumen saya ditolak?',
                'answer' => 'Periksa alasan penolakan yang diberikan oleh admin di detail dokumen. Alasan umum meliputi: informasi tidak lengkap, kualitas PDF buruk, format salah, bagian yang diperlukan hilang, atau pelanggaran kebijakan. Perbaiki masalah dan ajukan ulang.'
            ],
            [
                'question' => 'Bisakah saya menandatangani dokumen yang sama beberapa kali?',
                'answer' => 'Tidak, setiap dokumen hanya dapat ditandatangani sekali. Setelah Anda menandatangani dan status menjadi "Selesai", tanda tangan bersifat final. Jika Anda memerlukan salinan tertandatangan lain, unduh dari "Tanda Tangan Saya".'
            ],
            [
                'question' => 'Bagaimana jika saya membuat kesalahan saat memposisikan QR code?',
                'answer' => 'Sebelum mengklik "Tanda Tangani Dokumen", Anda dapat memindahkan QR code sebanyak yang Anda perlukan. Gunakan fungsi pratinjau untuk memeriksa posisi. Setelah Anda mengonfirmasi penandatanganan, posisi bersifat final.'
            ],
            [
                'question' => 'Apakah tanda tangan digital saya sah secara hukum?',
                'answer' => 'Tanda tangan digital yang dibuat oleh sistem ini menggunakan enkripsi RSA-2048 dan hashing SHA-256, yang merupakan metode kriptografi standar industri. Namun, keabsahan hukum tergantung pada hukum Indonesia dan kasus penggunaan spesifik Anda. Konsultasikan dengan penasihat hukum jika diperlukan.'
            ],
            [
                'question' => 'Bisakah saya menggunakan dokumen tertandatangan saya untuk tujuan resmi?',
                'answer' => 'Ya, dokumen tertandatangan dari sistem ini diterima oleh UMT Informatika untuk tujuan akademik. Untuk penggunaan eksternal, periksa dengan institusi penerima apakah mereka menerima dokumen tertandatangan secara digital.'
            ],
            [
                'question' => 'Apa yang terjadi jika saya kehilangan dokumen tertandatangan saya?',
                'answer' => 'Jangan khawatir! Semua dokumen tertandatangan disimpan secara permanen di sistem. Buka menu "Tanda Tangan Saya" dan unduh dokumen Anda lagi. Anda dapat mengunduhnya tanpa batas.'
            ],
            [
                'question' => 'Bagaimana orang lain dapat memverifikasi dokumen tertandatangan saya?',
                'answer' => 'Siapa pun dapat memverifikasi dengan: (1) Memindai QR code yang tertanam di PDF Anda menggunakan kamera ponsel atau aplikasi pemindai QR, atau (2) Mengunjungi halaman verifikasi publik dan memasukkan kode verifikasi. Halaman verifikasi akan menampilkan apakah tanda tangan valid.'
            ],
            [
                'question' => 'Apa yang harus saya lakukan jika QR code tidak dapat dipindai?',
                'answer' => 'Pastikan: (1) Dokumen tidak terlalu buram atau kualitas rendah saat dicetak, (2) QR code tidak tertutup atau terpotong, (3) Pemindai memiliki pencahayaan yang baik, (4) Coba aplikasi pemindai QR yang berbeda. Jika masalah berlanjut, unduh salinan baru dari sistem.'
            ]
        ];
    }
}
