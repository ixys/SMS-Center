<?php

return [
    // Commentaire FR : paramètres de connexion SMPP au GoIP4
    'host'       => env('SMPP_HOST', '192.168.1.142'),
    'port'       => (int) env('SMPP_PORT', 7777),
    'system_id'  => env('SMPP_SYSTEM_ID', 'goip'),
    'password'   => env('SMPP_PASSWORD', 'secret'),
    'system_type'=> env('SMPP_SYSTEM_TYPE', ''),

    // TON/NPI pour l'émetteur et le destinataire
    'source_ton' => env('SMPP_SOURCE_TON', 0),   // alphanumerique / national / etc.
    'source_npi' => env('SMPP_SOURCE_NPI', 0),
    'dest_ton'   => env('SMPP_DEST_TON', 1),     // international
    'dest_npi'   => env('SMPP_DEST_NPI', 1),     // ISDN / E.164

    // Keep-alive en secondes
    'keepalive_interval' => env('SMPP_KEEPALIVE_INTERVAL', 30),
];
