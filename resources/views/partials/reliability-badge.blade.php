@php
    $score = $shop->reliability;
    [$color, $icon] = match (true) {
        $score === null => ['secondary', 'bi-patch-question'],
        $score >= 90    => ['success', 'bi-shield-fill-check'],
        $score >= 75    => ['primary', 'bi-shield-check'],
        $score >= 50    => ['warning', 'bi-shield-exclamation'],
        default         => ['danger', 'bi-shield-x'],
    };
@endphp
<span class="badge text-bg-{{ $color }} ms-reliability" title="{{ __('Fiabilité du vendeur') }}">
    <i class="bi {{ $icon }} me-1"></i>@if($score === null){{ __('Nouveau') }}@else{{ $score }}% {{ __('fiable') }}@endif
</span>
