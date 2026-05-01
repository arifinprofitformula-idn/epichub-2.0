@php($badges = $badges ?? [])

<div class="flex flex-wrap items-center gap-1.5">
    @foreach ($badges as $badge)
        <x-ui.badge :variant="$badge['variant']">
            {{ $badge['label'] }}
        </x-ui.badge>
    @endforeach
</div>
