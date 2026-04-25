<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ $rendered['metaTitle'] }}</title>
        @if (filled($rendered['metaDescription']))
            <meta name="description" content="{{ $rendered['metaDescription'] }}" />
        @endif
    </head>
    <body>
        {!! $rendered['html'] !!}
    </body>
</html>
