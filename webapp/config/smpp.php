<?php

return [
    // Commentaire FR : paramètres de connexion SMPP au GoIP4
    'host'       => env('SMS_SMPP_HOST', '192.168.1.142'),
    'port'       => (int) env('SMS_SMPP_PORT', 7777),
    'system_id'  => env('SMS_SMPP_SYSTEM_ID', 'goip'),
    'password'   => env('SMS_SMPP_PASSWORD', 'secret'),
    'system_type'=> env('SMS_SMPP_SYSTEM_TYPE', ''),

    // TON/NPI pour l'émetteur et le destinataire
    'source_ton' => env('SMS_SMPP_SOURCE_TON', 0),   // alphanumerique / national / etc.
    'source_npi' => env('SMS_SMPP_SOURCE_NPI', 0),
    'dest_ton'   => env('SMS_SMPP_DEST_TON', 1),     // international
    'dest_npi'   => env('SMS_SMPP_DEST_NPI', 1),     // ISDN / E.164

    // Keep-alive en secondes
    'keepalive_interval' => env('SMS_SMPP_KEEPALIVE_INTERVAL', 30),
];
