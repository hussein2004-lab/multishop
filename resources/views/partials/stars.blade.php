@php $rating = (float) ($rating ?? 0); @endphp
<span class="text-warning text-nowrap">
    @for ($i = 1; $i <= 5; $i++)
        <i class="bi {{ $rating >= $i ? 'bi-star-fill' : ($rating >= $i - 0.5 ? 'bi-star-half' : 'bi-star') }}"></i>
    @endfor
</span>
