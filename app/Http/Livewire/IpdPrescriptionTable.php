<?php

namespace App\Http\Livewire;

use App\Models\IpdPrescription;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;

class IpdPrescriptionTable extends LivewireTableComponent
{
    public $ipdPrescriptionId;

    public $showButtonOnHeader = true;

    public $buttonComponent = 'ipd_prescriptions.add-button';

    protected $model = IpdPrescription::class;

    protected $listeners = ['refresh' => '$refresh', 'changeFilter', 'resetPage'];

    public function resetPage($pageName = 'page')
    {
        $rowsPropertyData = $this->getRows()->toArray();
        $prevPageNum = $rowsPropertyData['current_page'] - 1;
        $prevPageNum = $prevPageNum > 0 ? $prevPageNum : 1;
        $pageNum = count($rowsPropertyData['data']) > 0 ? $rowsPropertyData['current_page'] : $prevPageNum;

        $this->setPage($pageNum, $pageName);
    }

    public function mount(int $ipdPrescriptionId)
    {
        $this->ipdPrescriptionId = $ipdPrescriptionId;
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('ipd_prescriptions.created_at', 'desc')
            ->setQueryStringStatus(false);
        $this->setThAttributes(function (Column $column) {
            if ($column->isField('id')) {
                return [
                    'class' => 'text-center',
                ];
            }

            return [];
        });
        $this->setTdAttributes(function (Column $column, $row, $columnIndex, $rowIndex) {
            if ($column->isField('id')) {
                return [
                    'class' => 'text-center',
                ];
            }
            if ($column->isField('ipd_patient_department_id') || $column->isField('created_at')) {
                return [
                    'class' => 'pt-5',
                ];
            }

            return [];
        });
    }

    public function columns(): array
    {
        if (! getLoggedinPatient()) {
            $actionButton = Column::make(__('messages.common.action'), 'id')
                ->view('ipd_prescriptions.columns.action');
        } else {
            $actionButton = Column::make(__('messages.common.action'), 'id')
                ->view('ipd_prescriptions.columns.action');
        }

        return [
            Column::make(__('messages.common.created_on'), 'created_at')
                ->view('ipd_prescriptions.columns.created_at')
                ->sortable()
                ->searchable(),
            Column::make(__('messages.doctor_opd_charge.doctor'), 'patient.doctor.doctorUser.first_name')
                ->view('prescriptions.columns.doctor')
                ->sortable()
                ->searchable(),
            $actionButton,
        ];
    }

    public function builder(): Builder
    {
        return IpdPrescription::with('patient.doctor')->where('ipd_patient_department_id', $this->ipdPrescriptionId)
            ->select('ipd_prescriptions.*');
    }
}
