<?php

namespace App\Repositories;

use App\Models\IpdPatientDepartment;
use App\Models\IpdPayment;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class IpdPaymentRepository
 *
 * @version September 12, 2020, 11:46 am UTC
 */
class IpdPaymentRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'ipd_patient_department_id',
        'amount',
        'date',
        'note',
        'payment_mode',
    ];

    /**
     * Return searchable fields
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return IpdPayment::class;
    }

    public function store(array $input)
    {
        try {
            /** @var IpdPayment $ipdPayment */
            $ipdPayment = $this->create($input);

            // update ipd bill
            $ipdPatientDepartment = IpdPatientDepartment::findOrFail($input['ipd_patient_department_id']);
            $ipdBill = $ipdPatientDepartment->bill;
            if ($ipdBill) {
                $amount = $ipdPayment->amount;
                $ipdBill->total_payments = $ipdBill->total_payments + $amount;
                $ipdBill->net_payable_amount = $ipdBill->net_payable_amount - $amount;
                $ipdBill->save();
            }
            if (isset($input['file']) && ! empty($input['file'])) {
                $ipdPayment->addMedia($input['file'])->toMediaCollection(IpdPayment::IPD_PAYMENT_PATH,
                    config('app.media_disc'));
            }
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function updateIpdPayment(array $input, int $ipdPaymentId)
    {
        try {
            /** @var IpdPayment $ipdPayment */
            $ipdPayment = $this->update($input, $ipdPaymentId);
            if (isset($input['file']) && ! empty($input['file'])) {
                $ipdPayment->clearMediaCollection(IpdPayment::IPD_PAYMENT_PATH);
                $ipdPayment->addMedia($input['file'])->toMediaCollection(IpdPayment::IPD_PAYMENT_PATH,
                    config('app.media_disc'));
            }
            if ($input['avatar_remove'] == 1 && isset($input['avatar_remove']) && ! empty($input['avatar_remove'])) {
                removeFile($ipdPayment, IpdPayment::IPD_PAYMENT_PATH);
            }
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function deleteIpdPayment(int $ipdPaymentId)
    {
        try {
            /** @var IpdPayment $ipdPayment */
            $ipdPayment = $this->find($ipdPaymentId);
            $ipdPayment->clearMediaCollection(IpdPayment::IPD_PAYMENT_PATH);
            $this->delete($ipdPaymentId);
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }
}
