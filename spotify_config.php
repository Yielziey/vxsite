<?php
define('SPOTIFY_CLIENT_ID', getenv('SPOTIFY_CLIENT_ID') ?: '');
define('SPOTIFY_CLIENT_SECRET', getenv('SPOTIFY_CLIENT_SECRET') ?: '');

// Mag-check kung may laman ba ang credentials bago gamitin
if (empty(SPOTIFY_CLIENT_ID) || empty(SPOTIFY_CLIENT_SECRET)) {

}
?>