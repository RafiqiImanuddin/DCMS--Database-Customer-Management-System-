<?php
// Simpan kredensial di backend. Jangan dimasukkan ke browser atau localStorage.
return [
    'api_key' => getenv('WATZAP_API_KEY') ?: '',
    'number_key' => getenv('WATZAP_NUMBER_KEY') ?: '',
];
