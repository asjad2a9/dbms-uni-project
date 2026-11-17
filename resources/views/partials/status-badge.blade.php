@php
    $map = [
        'active' => 'success', 'inactive' => 'secondary', 'suspended' => 'warning', 'expired' => 'danger',
        'cancelled' => 'dark', 'new' => 'primary', 'contacted' => 'info', 'converted' => 'success', 'closed' => 'secondary',
    ];
@endphp
<span class="badge badge-soft-{{ $map[$status] ?? 'secondary' }} text-capitalize">{{ str_replace('_', ' ', $status) }}</span>
