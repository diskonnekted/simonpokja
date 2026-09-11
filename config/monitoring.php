<?php

// Ambang indikator deadline & pengaturan monitoring lainnya.

return [
    // Indikator deadline pekerjaan (dihitung dari tanggal_selesai).
    // 'mendesak' lebih parah daripada 'mendekati'.
    'deadline' => [
        'mendesak' => 7,   // sisa <= 7 hari -> MENDESAK
        'mendekati' => 30, // sisa <= 30 hari -> MENDEKATI DEADLINE
    ],
];
