<?php

namespace App\Helpers;

use App\Models\Complaint;

class WhatsAppLink
{
    public static function forComplaint(Complaint $complaint): string
    {
        $raw = $complaint->room?->pic?->phone_number ?? '';
        $normalized = preg_replace('/[^0-9]/', '', (string) $raw);

        if ($normalized === '') {
            return '';
        }

        if (str_starts_with($normalized, '0')) {
            $normalized = substr($normalized, 1);
        }

        if (! str_starts_with($normalized, '62')) {
            $normalized = '62'.ltrim($normalized, '0');
        }

        $text = urlencode('Peringatan: pengaduan di ruang '.($complaint->room?->room_name ?? 'ruang').' masih belum diperbaiki. Mohon ditangani segera.');

        return 'https://wa.me/'.$normalized.'?text='.$text;
    }
}
