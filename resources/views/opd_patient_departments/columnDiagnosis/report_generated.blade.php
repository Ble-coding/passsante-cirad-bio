@if ($row->report_generated == 0)
    <span class="badge bg-light-danger">{{ __('messages.common.no') }}</span>
@else
    <span class="badge bg-light-success">{{ __('messages.common.yes') }}</span>
@endif
