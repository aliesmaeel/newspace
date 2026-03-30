@php
    $zoomMeetingUrl = app(\App\Services\IntegrationSettingsService::class)->zoomMeetingUrl();
@endphp
@if ($zoomMeetingUrl !== '')
    <li><strong>Zoom:</strong> <a href="{{ $zoomMeetingUrl }}">{{ $zoomMeetingUrl }}</a></li>
@endif
