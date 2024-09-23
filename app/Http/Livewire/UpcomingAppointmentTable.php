<?php

namespace App\Http\Livewire;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Rappasoft\LaravelLivewireTables\Views\Column;

class UpcomingAppointmentTable extends LivewireTableComponent
{
    use WithPagination;

    protected $model = Appointment::class;

    public $showButtonOnHeader = false;

    public $showFilterOnHeader = false;

    public $buttonComponent = 'appointments.add-button';

    protected $listeners = ['refresh' => '$refresh'];

    public function resetPage($pageName = 'page')
    {
        $rowsPropertyData = $this->getRows()->toArray();
        $prevPageNum = $rowsPropertyData['current_page'] - 1;
        $prevPageNum = $prevPageNum > 0 ? $prevPageNum : 1;
        $pageNum = count($rowsPropertyData['data']) > 0 ? $rowsPropertyData['current_page'] : $prevPageNum;

        $this->setPage($pageNum, $pageName);
    }

    public function configure(): void
    {
        $this->setDefaultSort('appointments.created_at', 'desc');
        $this->setQueryStringStatus(false);
        $this->setPrimaryKey('id');
        $this->setSearchVisibilityDisabled();
    }

    public function columns(): array
    {
        return [
            Column::make(__('messages.case.patient'), 'patient.patientUser.email')
                ->hideIf('patient.patientUser.email'),
            Column::make(__('messages.case.patient'), 'doctor.doctorUser.email')
                ->hideIf('doctor.doctorUser.email'),
            Column::make(__('messages.case.patient'), 'patient.patientUser.first_name')
                ->view('appointments.columns.patient_name')
                ->sortable(
                    function (Builder $query, $direction) {
                        return $query->orderBy(User::select('first_name')->whereColumn('id', 'patients.user_id'), $direction);
                    }
                ),
            Column::make(__('messages.case.doctor'), 'doctor.doctorUser.first_name')
                ->view('appointments.columns.doctor_name')
                ->sortable(
                    function (Builder $query, $direction) {
                        return $query->orderBy(User::select('first_name')->whereColumn('id', 'doctors.user_id'), $direction);
                    }
                ),
            Column::make(__('messages.case.patient'), 'patient_id')->hideIf(1),
            Column::make(__('messages.case.doctor'), 'doctor_id')->hideIf(1),
            Column::make(__('messages.appointment.date'), 'opd_date')
                ->view('appointments.columns.date')
                ->sortable(),
        ];
    }

    public function builder(): Builder
    {
        $now = Carbon::now();
        $sixDays = $now->copy()->addDays(6);
        $query = Appointment::with(['patient.user', 'doctor.user'])->whereBetween('opd_date',[$now, $sixDays])->select('appointments.*');

        return $query;
    }
}
