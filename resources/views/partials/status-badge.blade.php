{{-- Badge status: $status (Lulus / Tidak Lulus) --}}
@if ($status === 'Lulus')
    <span class="badge badge-lulus">● Lulus</span>
@else
    <span class="badge badge-tidak">● Tidak Lulus</span>
@endif
