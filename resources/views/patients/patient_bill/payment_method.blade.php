@php
    $manualPayment = App\Models\BillTransaction::where('bill_id', $row->id)
        ->latest()
        ->first();
@endphp
@if ($row->status == 0 || empty($row->status))
    <div class="w-150px d-flex align-items-center">
        <select class="form-select make-bill-payment" data-id="{{ $row->id }}" data-control="select2">
            <option value="0">
                {{ __('Select Payment Type') }}
            </option>
            <option value="1">
                {{ __('messages.setting.stripe') }}
            </option>
            <option value="2">
                {{ __('messages.transaction_filter.manual') }}
            </option>
        </select>
    </div>
@elseif ($manualPayment->payment_type == 2)
    <span class="badge bg-light-warning">{{ __('messages.transaction_filter.manual') }}</span>
@else
    <span class="badge bg-light-primary">{{ __('messages.setting.stripe') }}</span>
@endif
