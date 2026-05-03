<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ $rendered['metaTitle'] }}</title>
        @if (filled($rendered['metaDescription']))
            <meta name="description" content="{{ $rendered['metaDescription'] }}" />
        @endif

        <meta property="og:type" content="website" />
        <meta property="og:title" content="{{ $rendered['metaTitle'] }}" />
        @if (filled($rendered['metaDescription']))
            <meta property="og:description" content="{{ $rendered['metaDescription'] }}" />
        @endif
        <meta property="og:url" content="{{ url()->current() }}" />
        @if (filled($rendered['metaImage']))
            <meta property="og:image" content="{{ $rendered['metaImage'] }}" />
            <meta property="twitter:card" content="summary_large_image" />
            <meta property="twitter:title" content="{{ $rendered['metaTitle'] }}" />
            @if (filled($rendered['metaDescription']))
                <meta property="twitter:description" content="{{ $rendered['metaDescription'] }}" />
            @endif
            <meta property="twitter:image" content="{{ $rendered['metaImage'] }}" />
            <link rel="image_src" href="{{ $rendered['metaImage'] }}" />
        @else
            <meta property="twitter:card" content="summary" />
        @endif
    </head>
    <body>
        {!! $rendered['html'] !!}
    </body>
</html>
