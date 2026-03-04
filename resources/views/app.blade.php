<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>NPS Enquestes - CRIL</title>
        <link href='https://api.mapbox.com/mapbox-gl-js/v3.2.0/mapbox-gl.css' rel='stylesheet' />
        @vite(['resources/js/app.js', 'resources/css/app.css'])
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
