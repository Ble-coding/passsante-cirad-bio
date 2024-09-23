<?php

namespace App\Repositories;

use App\Models\Medicine;
use App\Models\MedicineBill;
use App\Models\SaleMedicine;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class DoctorRepository
 *
 * @version February 13, 2020, 8:55 am UTC
 */
class MedicineBillRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'to',
        'subject',
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
        return MedicineBill::class;
    }

    public function update($medicineBill, $input): bool
    {
        try {
            DB::beginTransaction();
            $input['payment_status'] = isset($input['payment_status']) ? 1 : $medicineBill->payment_status;
            foreach ($input['medicine'] as $key => $inputSale) {
                if (empty($input['medicine'][$key]) && $input['payment_status'] == false) {

                    throw new UnprocessableEntityHttpException(__('messages.medicine_bills.medicine_not_selected'));
                }
                $saleMedincine = SaleMedicine::where('medicine_bill_id', $input['medicine_bill'])->where('medicine_id', $input['medicine'][$key])->first();
                if (isset($saleMedincine->sale_quantity) && $input['quantity'][$key]) {
                    if ($saleMedincine->sale_quantity < $input['quantity'][$key] && $input['payment_status'] == 1) {

                        throw new UnprocessableEntityHttpException(__('messages.medicine_bills.update_quantity'));
                    }
                }
            }

            $medicineBill->load('saleMedicine');
            $previousMedicineIds = $medicineBill->saleMedicine->pluck('medicine_id');
            $previousMedicineArray = [];
            foreach ($previousMedicineIds as $previousMedicineId) {
                $previousMedicineArray[] = $previousMedicineId;
            }
            // dump($previousMedicineArray);
            // dump($input['medicine']);
            $deleteIds = array_diff($previousMedicineArray, $input['medicine']);
            if ($input['payment_status'] && $medicineBill->payment_status == true) {
                foreach ($deleteIds as $key => $value) {
                    if(array_key_exists($key,$input['medicine'])){
                        $updatedMedicine = Medicine::find($input['medicine'][$key]);
                        if ($updatedMedicine->available_quantity < $input['quantity'][$key]) {
                            $available = $updatedMedicine->available_quantity == null ? 0 : $updatedMedicine->available_quantity;

                            throw new UnprocessableEntityHttpException(__('messages.medicine_bills.available_quantity') . ' ' . $updatedMedicine->name . ' ' . __('messages.medicine_bills.is') . ' ' . $available . '.');
                        }
                    }

                }
                foreach ($deleteIds as $deleteId) {
                    $deleteMedicine = Medicine::find($deleteId);
                    $saleMedicine = SaleMedicine::where('medicine_bill_id', $medicineBill->id)->where('medicine_id', $deleteId)->first();
                    $deleteMedicine->update(['available_quantity' => $deleteMedicine->available_quantity + $saleMedicine->sale_quantity]);
                }
                foreach ($deleteIds as $key => $value) {
                    $updatedMedicine = Medicine::find($input['medicine'][$key]);
                    $updatedMedicine->update([
                        'available_quantity' => $updatedMedicine->available_quantity - $input['quantity'][$key],
                    ]);
                }
            }
            $arr = collect($input['medicine']);
            $duplicateIds = $arr->duplicates();
            $prescriptionMedicineArray = [];
            $inputdoseAndMedicine = [];
            foreach ($medicineBill->saleMedicine as $saleMedicine) {
                $prescriptionMedicineArray[$saleMedicine->medicine_id] = $saleMedicine->sale_quantity;
            }

            foreach ($input['medicine'] as $key => $value) {
                $inputdoseAndMedicine[$value] = $input['quantity'][$key];
            }
            foreach ($input['medicine'] as $key => $value) {
                $result = array_intersect($prescriptionMedicineArray, $inputdoseAndMedicine);

                $medicine = Medicine::find($input['medicine'][$key]);
                if (! empty($duplicateIds)) {
                    foreach ($duplicateIds as $key => $value) {
                        $medicine = Medicine::find($duplicateIds[$key]);

                        throw new UnprocessableEntityHttpException(__('messages.medicine_bills.duplicate_medicine'));
                    }
                }
                $saleMedicine = SaleMedicine::where('medicine_bill_id', $medicineBill->id)->where('medicine_id', $medicine->id)->first();
                $qty = $input['quantity'][$key];
                if ($input['payment_status'] == true && $medicine->available_quantity < $qty && $medicineBill->payment_status == 0) {
                    $available = $medicine->available_quantity == null ? 0 : $medicine->available_quantity;

                    throw new UnprocessableEntityHttpException(__('messages.medicine_bills.available_quantity').' '.$medicine->name.' '.__('messages.medicine_bills.is').' '.$available.'.');
                }
                if (! is_null($saleMedicine) && $input['payment_status'] == 1 && $medicineBill['payment_status'] == 1) {
                    $PreviousQty = $saleMedicine->sale_quantity == null ? 0 : $saleMedicine->sale_quantity;
                    if ($PreviousQty > $qty) {
                        $medicine->update([
                            'available_quantity' => $medicine->available_quantity + $PreviousQty - $qty,
                        ]);
                    }
                }

                if (! array_key_exists($input['medicine'][$key], $result) && $medicine->available_quantity < $qty && $input['payment_status'] == false) {
                    $available = $medicine->available_quantity == null ? 0 : $medicine->available_quantity;

                    throw new UnprocessableEntityHttpException(__('messages.medicine_bills.available_quantity').' '.$medicine->name.' '.__('messages.medicine_bills.is').' '.$available.'.');
                }

            }
            $medicineBill->saleMedicine()->delete();

            $beforeStatus = $medicineBill['payment_status'];
            $medicineBill->Update([
                'patient_id' => $input['patient_id'],
                'net_amount' => $input['net_amount'],
                'discount' => $input['discount'],
                'payment_status' => $input['payment_status'],
                'payment_type' => $input['payment_type'],
                'total' => $input['total'],
                'tax_amount' => $input['tax'],
                'note' => $input['note'],
                'bill_date' => $input['bill_date'],
            ]);
            if ($input['category_id']) {
                foreach ($input['category_id'] as $key => $value) {
                    $medicine = Medicine::find($input['medicine'][$key]);
                    SaleMedicine::create([
                        'medicine_bill_id' => $medicineBill->id,
                        'medicine_id' => $medicine->id,
                        'sale_price' => $input['sale_price'][$key],
                        'expiry_date' => $input['expiry_date'][$key],
                        'sale_quantity' => $input['quantity'][$key],
                        'tax' => $input['tax_medicine'][$key] == null ? 0 : $input['tax_medicine'][$key],

                    ]);

                    if ($input['payment_status'] == 1 && $beforeStatus == 0) {
                        $medicine->update([
                            'available_quantity' => $medicine->available_quantity - $input['quantity'][$key],
                        ]);
                    }
                }
            }
            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return true;
    }
}
