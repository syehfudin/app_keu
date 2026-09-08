<?php

return [
    'client_id' => env('GOOGLE_CLIENT_ID', ''),
    'spread_sheet_id' => env('SPREADSHEET_ID', ''),
    'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
    'redirect_uri' => env('GOOGLE_REDIRECT', ''),
    'scopes' => [\Google\Service\Sheets::DRIVE, \Google\Service\Sheets::SPREADSHEETS],
    'access_type' => 'online',
    'approval_prompt' => 'auto',
    'prompt' => 'consent', //"none", "consent", "select_account" default:none
];
