@props(['type' => 'success', 'message'])

@php
    $icons = [
        'success' => 'bi-check-circle-fill',
        'danger'  => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        'info'    => 'bi-info-circle-fill',
    ];
    $icon = $icons[$type] ?? 'bi-info-circle-fill';
@endphp

<div class="alert alert-{{ $type }} alert-dismissible fade show auto-dismiss" role="alert">
    <i class="bi {{ $icon }} me-1"></i> {{ $message }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<script>
    document.querySelectorAll('.auto-dismiss').forEach(function (el) {
        setTimeout(function () {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(el);
            bsAlert.close();
        }, 4000);
    });
</script>
