### Spesifikasi Kebutuhan Sistem (SRS): Aplikasi Monitoring Kinerja Pokja LPSE Terintegrasi

#### 1\. Pendahuluan dan Signifikansi Strategis

Digitalisasi pemantauan Kelompok Kerja (Pokja) Pemilihan merupakan langkah fundamental dalam menjaga integritas pengadaan barang dan jasa pemerintah. Di tengah kompleksitas  *e-procurement*  saat ini, transparansi bukan sekadar fitur tambahan, melainkan pilar utama dalam mitigasi risiko kolusi dan intervensi manusia yang tidak akuntabel. Tanpa sistem monitoring yang mumpuni, pimpinan Lembaga Pengadaan Secara Elektronik (LPSE) seringkali kehilangan visibilitas terhadap proses yang tengah berjalan, yang berujung pada sulitnya melakukan audit terhadap keputusan-keputusan krusial.Mengadopsi pembelajaran dari ekosistem  *eProc by Promise* , tantangan terbesar pada sistem konvensional adalah minimnya jejak audit ( *audit trail* ) yang dapat direkonstruksi. Hal ini menciptakan celah bagi praktik manipulasi dokumen atau pengambilan keputusan subjektif tanpa landasan data yang objektif. Sistem monitoring terintegrasi ini hadir untuk menjawab tantangan tersebut dengan menyediakan visibilitas  *real-time*  bagi pimpinan, memastikan setiap tahapan pengadaan terdokumentasi secara digital, sistematis, dan selaras dengan regulasi nasional guna menjamin tanggung-gugat (akuntabilitas) di mata publik.

#### 2\. Landasan Hukum dan Aturan Keanggotaan Pokja

Sistem ini dibangun dengan menjadikan regulasi sebagai standar operasional prosedur (SOP) digital yang kaku. Hal ini dilakukan untuk memastikan bahwa setiap tim Pokja yang dibentuk memiliki legalitas hukum yang kuat sejak awal proses.

##### Sintesis Regulasi

Logika bisnis aplikasi ini didasarkan pada poin-poin tanggung jawab Pokja sesuai dengan:

* **Perpres No. 16 Tahun 2018 (jo. Perpres No. 12 Tahun 2021):**  Landasan utama mengenai kewenangan Pokja Pemilihan dalam persiapan dan pelaksanaan pemilihan penyedia.  
* **Peraturan LKPP:**  Standarisasi prosedur evaluasi kualifikasi, administrasi, dan teknis yang harus divalidasi oleh sistem secara otomatis.

##### Parameter Keanggotaan Pokja

Sistem akan melakukan validasi otomatis pada modul pembentukan tim dengan spesifikasi sebagai berikut:| Parameter | Aturan Validasi Sistem || \------ | \------ || **Jumlah Anggota** | Minimal 3 (tiga) orang dan wajib berjumlah ganjil. || **Status Jabatan** | Wajib merupakan Pejabat Fungsional Pengelola Pengadaan Barang/Jasa (PPBJ). || **Lokasi Unit Kerja** | Terdaftar aktif pada Unit Kerja Pengadaan Barang/Jasa (UKPBJ). || **Sifat Penugasan** | Penugasan bersifat  *ad-hoc*  (per paket tender) dengan validasi durasi tugas. |

Kegagalan memenuhi salah satu parameter di atas akan menyebabkan sistem menolak pembentukan tim secara otomatis, memastikan kepatuhan penuh terhadap regulasi sejak tahap inisiasi.

#### 3\. Ruang Lingkup Integrasi Modul Pengawasan

Modul-modul dalam sistem ini dirancang untuk memantau seluruh siklus hidup pengadaan secara  *end-to-end* , meminimalisir celah gelap informasi di setiap tahapan.

1. **Modul Reviu Dokumen Pemilihan**  Memfasilitasi penyelarasan spesifikasi teknis, Kerangka Acuan Kerja (KAK), Harga Perkiraan Sendiri (HPS), dan rancangan kontrak bersama PPK. Modul ini dirancang untuk menangani proyek skala besar dengan risiko tinggi, seperti proyek pembangunan RSUD Tipe D Wanayasa dengan pagu anggaran  **Rp57 Miliar**  yang bersifat  *multi-year*  (2026-2027). Setiap hasil reviu harus terdokumentasi sebelum tender ditayangkan.  
2. **Modul Pemantauan Evaluasi Penawaran**  Mengawasi proses pembuktian kualifikasi. Sistem memberikan pengawasan ketat pada evaluasi 3 penawar teratas. Fitur ini dirancang untuk melacak alasan diskualifikasi secara detail, terutama jika pemenang tender jatuh pada peringkat jauh di bawah (misalnya  **Peringkat 11 dari 11 peserta** ), guna memastikan tidak adanya pengaturan pemenang cadangan yang disengaja.  
3. **Modul Penanganan Sanggahan**  Mendokumentasikan tanggapan formal terhadap sanggahan peserta dan mencatat alasan teknis di balik keputusan tender gagal atau batal secara transparan.  
4. **Modul Audit Trail Immutable**  Menggunakan mekanisme pencatatan otomatis terhadap setiap aksi pengguna. Sistem akan mengambil  **snapshot JSON**  ( *old/new values* ) yang mencakup  **Alamat IP**  dan  **Stempel Waktu (Timestamp)**  yang akurat untuk setiap perubahan status data. Hal ini menjamin integritas data yang tidak dapat diubah (immutable) sebagai bukti digital jika diperlukan proses audit lebih lanjut.

#### 4\. Analisis Kebutuhan Fungsional: Dashboard & Early Warning

Sistem ini mengubah data mentah menjadi wawasan strategis bagi pimpinan LPSE melalui analitik tingkat lanjut dan kendali akses yang ketat.

##### Sistem Peringatan Dini (Early Warning Anomali)

Sistem akan memicu peringatan otomatis ( *trigger* ) jika terdeteksi pola yang tidak wajar berdasarkan parameter studi kasus Jembatan di Banjarnegara:

* **Anomali Penurunan Harga:**  Peringatan aktif jika penurunan harga pemenang sangat rendah (misal:  **3,6%** ) sementara tren pasar umum mencapai  **20% atau lebih** .  
* **Diskualifikasi Masif:**  Peringatan jika pemenang tender berada pada  **Peringkat 11**  sementara peringkat 1 hingga 10 dinyatakan gugur tanpa alasan yang didukung bukti jejak audit yang kuat.  
* **Ketidakhadiran Klarifikasi:**  Deteksi otomatis terhadap pola peserta yang tidak hadir saat klarifikasi secara berulang pada paket-paket tertentu.

##### Dashboard KPI Real-Time

Dashboard pimpinan harus menyajikan visualisasi data yang akurat untuk pengambilan keputusan:| Indikator Kinerja Utama (KPI) | Deskripsi Fungsional | Fitur || \------ | \------ | \------ || **Status Proyek Aktif** | *Real-time*  monitoring tahap reviu, evaluasi, dan sanggah. | Grafik Status || **Efisiensi Pagu (Budget)** | Persentase penghematan anggaran terhadap HPS. | Tabel Optimalisasi || **Akuntabilitas Pokja** | Kecepatan respons dan kepatuhan durasi evaluasi. | Laporan Kinerja || **Reporting Terpadu** | Rekapitulasi data untuk keperluan audit eksternal. | **Ekspor PDF/CSV** |

##### Role-Based Access Control (RBAC)

* **Pimpinan:**  Akses penuh ke Dashboard, Monitoring Anomali, dan  **Hak Ekspor Laporan (PDF/CSV)** .  
* **Admin LPSE:**  Manajemen master data pengguna dan konfigurasi parameter sistem.  
* **Anggota Pokja:**  Akses terbatas hanya pada paket tender yang ditugaskan kepada mereka.

#### 5\. Arsitektur Teknis dan Desain Data

Arsitektur teknis dirancang untuk menjamin skalabilitas dan ketersediaan data yang tinggi melalui pemilihan  *tech stack*  yang teruji.

* **Bahasa & Framework:**   **PHP Laravel**  dengan pola  *Model-View-Controller*  (MVC) untuk memastikan pemisahan logika bisnis yang bersih.  
* **Basis Data:**   **MySQL Relational Database**  untuk menjaga integritas hubungan antar-entitas data ( *Product, History, User, Audit* ).

##### Mekanisme Update Data (Logika Logis)

Untuk memastikan Dashboard selalu mutakhir, sistem menerapkan dua mekanisme:

1. **Scheduled Task (Cron Job):**  Berjalan secara otomatis setiap hari pada pukul  **00:05**  untuk menghitung ulang nilai efisiensi anggaran dan status akumulatif seluruh paket.  
2. **Catch-up Logic:**  Pemicu otomatis di latar belakang yang berjalan saat Pokja melakukan pembaruan status evaluasi, sehingga Dashboard Pimpinan memberikan informasi terbaru tanpa intervensi manual tambahan.Setiap mutasi data pengadaan akan dicatat melalui alur otentikasi login yang ketat, memicu pembaruan otomatis pada laporan nilai/status sesuai dengan logika manajemen aset yang akuntabel.

#### 6\. Metodologi Pengembangan: Model Waterfall

Metodologi Waterfall dipilih untuk menjamin bahwa setiap spesifikasi kebutuhan hukum dan fungsional terpenuhi secara sempurna sebelum tahap selanjutnya dimulai.Tahapan pengembangan mengikuti model sistematis (modifikasi  **Saravanos et al** ):

1. **Analysis:**  Identifikasi spesifikasi teknis dan lingkungan operasi LPSE.  
2. **Design:**  Pemodelan UML ( *Use Case, Activity, ERD* ) dan perancangan struktur basis data yang mendukung  *audit trail* .  
3. **Implementation:**  Proses pengkodean menggunakan Laravel dan MySQL berdasarkan desain yang telah disetujui.  
4. **Testing:**  Menggunakan metode  **Black Box Testing**  untuk memvalidasi fungsionalitas antarmuka, keamanan akses (RBAC), dan keakuratan kalkulasi otomatis pada modul  *Early Warning*  tanpa adanya cacat fungsional.

#### 7\. Penutup dan Harapan Sistem

Aplikasi Monitoring Kinerja Pokja LPSE Terintegrasi ini bukan sekadar alat administratif, melainkan instrumen strategis untuk menciptakan ekosistem pengadaan yang kohesif dan bersih. Dengan mengintegrasikan parameter hukum ke dalam logika perangkat lunak dan menyediakan jejak audit yang tak terbantahkan, sistem ini melindungi Pokja yang berintegritas sekaligus memberikan pimpinan kendali penuh dalam mendeteksi anomali secara dini. Keberhasilan implementasi sistem ini diharapkan dapat memulihkan kepercayaan publik terhadap proses pengadaan barang/jasa pemerintah melalui transparansi yang dapat dipertanggungjawabkan secara digital dan dilindungi oleh kekuatan hukum yang terintegrasi.

&nbsp;