<?php

namespace App\Http\Controllers;

use App\Models\IpdPatientDepartment;
use App\Repositories\PatientPaystackRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Laracasts\Flash\Flash;
use Unicodeveloper\Paystack\Facades\Paystack;

class PatientPaystackController extends AppBaseController
{
    /**
     * @var PatientPaystackRepository
     */
    private $patientPaystackRepository;

    /**
     * PatientPaytmController constructor.
     */
    public function __construct(PatientPaystackRepository $patientPaystackRepository)
    {
        $this->patientPaystackRepository = $patientPaystackRepository;

        config(['paystack.publicKey' => getSelectedPaymentGateway('paystack_public_key'),
            'paystack.secretKey' => getSelectedPaymentGateway('paystack_secret_key'),
            'paystack.paymentUrl' => 'https://api.paystack.co',
        ]);
    }

    public function redirectToGateway(Request $request)
    {
        if (strtoupper(getCurrentCurrency()) != 'ZAR') {
            Flash::error(__('messages.new_change.paystack_support_zar'));

            return redirect(route('patient.ipd'));
        }

        $amount = $request->get('amount');
        $ipdNumber = $request->get('ipdNumber');
        $ipdPatientId = IpdPatientDepartment::whereIpdNumber($ipdNumber)->first()->id;

        try {
            $request->merge([
                'email' => getLoggedInUser()->email, // email of recipients
                'orderID' => $ipdPatientId, // anything
                'amount' => $amount * 100,
                'quantity' => 1, // always 1
                'currency' => 'ZAR',
                'reference' => Paystack::genTranxRef(),
                'metadata' => json_encode(['ipd_patient_id' => $ipdPatientId]), // this should be related data
            ]);

            return Paystack::getAuthorizationUrl()->redirectNow();
        } catch (\Exception $e) {
            Flash::error(__('messages.new_change.payment_fail'));

            return Redirect::back()->withMessage([
                'msg' => __('messages.new_change.paystack_token_expired'), 'type' => 'error',
            ]);
        }
    }

    public function handleGatewayCallback(Request $request): RedirectResponse
    {
        $paymentDetails = Paystack::getPaymentData();

        $this->patientPaystackRepository->patientPaymentSuccess($paymentDetails);

        Flash::success(__('messages.flash.your_payment_success'));

        return redirect(route('patient.ipd'));
    }
}
