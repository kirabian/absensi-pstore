<?php

/**
 * Mengubah "Fabian Syah Al Ghiffari" menjadi "FS"
 */
if (!function_exists('getInitials')) {
    function getInitials($name)
    {
        $words = explode(' ', $name);
        $initials = '';

        if (count($words) >= 2) {
            // Ambil huruf pertama dari 2 kata pertama
            $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else if (count($words) == 1) {
            // Ambil 2 huruf pertama jika hanya 1 kata
            $initials = strtoupper(substr($words[0], 0, 2));
        } else {
            // Default jika tidak ada nama
            $initials = '??';
        }

        return $initials;
    }
}
