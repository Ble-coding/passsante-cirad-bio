@extends('layouts.app')
@section('title')
    {{ __('messages.setting.payment_gateway') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-column">
            @include('flash::message')
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => 'super-admin-payment-gateway.update', 'class'=>'form','id'=>'adminPaymentForm']) }}
                    <div class="row">
                        {{--STRIPE --}}
                        <div class="col-12 d-flex align-items-center">
                            <span class="form-label my-3">{{ __('messages.setting.stripe') . ' :' }}</span>
                            <label class="form-check form-switch form-switch-sm ms-3">
                                <input type="checkbox" name="stripe_enable" class="form-check-input stripe-enable"
                                       value="1" {{ !empty($setting['stripe_enable']) == '1' ? 'checked' : ''}}
                                       id="stripeEnableAdmin">
                                <span class="custom-switch-indicator"></span>
                            </label>
                        </div>
                        <div class="stripe-div-admin col-12">
                            <div class="row">
                                <div class="form-group col-sm-6 mb-5">
                                    {{ Form::label('stripe_key', __('messages.setting.stripe_key').':', ['class' => 'form-label']) }}
                                    {{ Form::text('stripe_key',(!empty($setting['stripe_key'] )) ? $setting['stripe_key'] : null, ['class' => 'form-control' , 'id' => 'StripeAdminKey' , 'placeholder' => __('messages.setting.stripe_key')]) }}

                                </div>
                                <div class="form-group col-sm-6 mb-5">
                                    {{ Form::label('stripe_secret', __('messages.setting.stripe_secret').':', ['class' => 'form-label']) }}
                                    {{ Form::text('stripe_secret',!empty($setting['stripe_secret']) ? $setting['stripe_secret']: null, ['class' => 'form-control', 'id' => 'StripeAdminSecret' , 'placeholder' => __('messages.setting.stripe_secret')]) }}
                                </div>
                            </div>
                        </div>




{{--                                 PAYPAL --}}
                        <div class="col-12 d-flex align-items-center">
                            <span class="form-label my-3">{{ __('messages.setting.paypal') . ' :' }}</span>
                            <label class="form-check form-switch form-switch-sm ms-3">
                                <input type="checkbox" name="paypal_enable" class="form-check-input paypal-enable"
                                       value="1"
                                       {{ !empty($setting['paypal_enable'])  == '1' ? 'checked' : ''
}}
                                       id="paypalEnableAdmin">
                                <span class="custom-switch-indicator"></span>
                            </label>
                        </div>
                        <div class="paypal-div-admin col-12">
                            <div class="row">
                                <div class="form-group col-sm-6 mb-5">
                                    {{ Form::label('paypal_client_id', __('messages.setting.paypal_client_id').':', ['class' => 'form-label']) }}
                                    {{ Form::text('paypal_key', !empty($setting['paypal_key'])?$setting['paypal_key'] : null, ['class' => 'form-control','id' => 'paypalKeyAdmin' , 'placeholder' => __('messages.setting.paypal_client_id')]) }}
                                </div>
                                <div class="form-group col-sm-6 mb-5">
                                    {{ Form::label('paypal_secret', __('messages.setting.paypal_secret').':', ['class' => 'form-label']) }}
                                    {{ Form::text('paypal_secret',!empty($setting['paypal_secret']) ? $setting['paypal_secret'] : null,  ['class' => 'form-control','id' => 'paypalSecretAdmin' , 'placeholder' => __('messages.setting.paypal_secret')]) }}
                                </div>
                                <div class="form-group col-sm-6 mb-5">
                                    {{ Form::label('paypal_mode', __('messages.setting.paypal_mode').':', ['class' => 'form-label']) }}
                                    {{ Form::text('paypal_mode',!empty($setting['paypal_mode']) ? $setting['paypal_mode'] : null, ['class' => 'form-control','id' => 'paypalModeAdmin' , 'placeholder' => __('messages.setting.paypal_mode')]) }}
                                </div>
                            </div>
                        </div>
{{--                         Razorpay --}}
                        <div class="col-12 d-flex align-items-center">
                            <span class="form-label my-3">{{ __('messages.setting.razorpay'). ' :' }}</span>
                            <label class="form-check form-switch form-switch-sm ms-3">
                                <input type="checkbox" name="razorpay_enable" class="form-check-input razorpay_enable"
                                       value="1"
                                       {{ !empty($setting['razorpay_enable'])  == '1' ? 'checked' : ''
}}
                                       id="razorpayEnableAdmin">
                                <span class="custom-switch-indicator"></span>
                            </label>
                        </div>
                        <div class="razorpay-div-admin col-12">
                            <div class="row">
                                <div class="form-group col-sm-6 mb-5">

                                    {{ Form::label('razorpay_key', __('messages.setting.razorpay_key').':', ['class' => 'form-label']) }}
                                    {{ Form::text('razorpay_key',(!empty($setting['razorpay_key'])) ? $setting['razorpay_key'] : null, ['class' => 'form-control' , 'id' => 'razorpayKeyAdmin' , 'placeholder' => __('messages.setting.razorpay_key')]) }}

                                </div>
                                <div class="form-group col-sm-6 mb-5">
                                    {{ Form::label('razorpay_secret', __('messages.setting.razorpay_secret').':', ['class' => 'form-label']) }}
                                    {{ Form::text('razorpay_secret',!empty($setting['razorpay_key']) ? $setting['razorpay_secret']: null, ['class' => 'form-control', 'id' => 'razorpaySecretAdmin' , 'placeholder' => __('messages.setting.razorpay_secret')]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 d-flex align-items-center">
                            <span class="form-label my-3">{{ __('messages.transaction_filter.manual'). ' :' }}</span>
                            <label class="form-check form-switch form-switch-sm ms-3">
                                <input type="checkbox" name="razorpay_enable" class="form-check-input"
                                       value="1" checked disabled
                                       id="manualPay">
                                <span class="custom-switch-indicator"></span>
                            </label>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary"
                                    id="userCredentialSettingBtn">{{ __('messages.common.save') }}</button>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
{{--    <script src="{{ mix('assets/js/settings/credentials.js') }}"></script>--}}

