@foreach($bookings as $b)
@php $sc = match($b->status){
    'confirmed'=>'success','payment_pending'=>'warning',
    'in_progress'=>'primary','completed'=>'dark',
    'cancelled'=>'danger', default=>'secondary'
}; @endphp
<tr>
    <td class="fw-semibold">#{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}</td>
    <td>{{ optional($b->user)->name }}</td>
    <td>{{ optional($b->serviceProvider)->business_name }}</td>
    <td>{{ optional($b->service)->name }}</td>
    <td class="small text-muted">{{ optional($b->carModel)->name ?? '—' }}</td>
    <td class="small">{{ $b->appointment_time?->format('d M Y, h:i A') ?? '—' }}</td>
    <td class="fw-bold">PKR {{ number_format($b->final_price) }}</td>
    <td><span class="badge bg-{{ $sc }} rounded-pill text-capitalize">{{ str_replace('_',' ',$b->status) }}</span></td>
</tr>
@endforeach
