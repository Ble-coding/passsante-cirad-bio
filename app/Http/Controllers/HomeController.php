<?php

namespace App\Http\Controllers;

use App\Models\AdvancedPayment;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Enquiry;
use App\Models\Module;
use App\Models\NoticeBoard;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\SuperAdminCurrencySetting;
use App\Models\SuperAdminSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\DashboardRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class HomeController extends AppBaseController
{
    private $dashboardRepository;

    /**
     * Create a new controller instance.
     */
    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->middleware('auth');
        $this->dashboardRepository = $dashboardRepository;
    }

    /**
     * Show the application dashboard.
     *
     * @return Factory|View
     */
    public function index(): View
    {
        return view('home');
    }

    /**
     * @return Factory|View
     */
    public function dashboard(): View
    {
        $data['invoiceAmount'] = totalAmount();
        $data['billAmount'] = Bill::sum('amount');
        $data['paymentAmount'] = Payment::sum('amount');
        $data['advancePaymentAmount'] = AdvancedPayment::sum('amount');
        $data['doctors'] = Doctor::count();
        $data['patients'] = Patient::count();
        $data['nurses'] = Nurse::count();
        $data['availableBeds'] = Bed::whereIsAvailable(1)->count();
        $data['noticeBoards'] = NoticeBoard::take(5)->orderBy('id', 'DESC')->get();
        $data['enquiries'] = Enquiry::where('status', 0)->latest()->take(5)->get();
        $data['currency'] = Setting::CURRENCIES;
        $modules = Module::pluck('is_active', 'name')->toArray();

        return view('dashboard.index', compact('data', 'modules'));
    }

    public function superAdminDashboard(): View
    {
        $query = User::where('department_id', '=', User::USER_ADMIN)
            ->whereNotNull([
                'hospital_name',
                'username',
            ])->select('users.*');
        $data['users'] = $query->count();
        $data['revenue'] = Transaction::where('status', '=', Transaction::APPROVED)->sum('amount');
        $current_currency = SuperAdminSetting::where('key', '=', 'super_admin_currency')->first()->value;
        $data['currency'] = SuperAdminCurrencySetting::where('currency_code', strtoupper($current_currency))->first();
        $data['activeHospitalPlan'] = $this->dashboardRepository->getTotalActiveDeActiveHospitalPlans()['activePlansCount'];
        $data['deActiveHospitalPlan'] = $this->dashboardRepository->getTotalActiveDeActiveHospitalPlans()['deActivePlansCount'];

        return view('super_admin.dashboard.index', compact('data'));
    }

    public function incomeExpenseReport(Request $request): JsonResponse
    {
        $data = $this->dashboardRepository->getIncomeExpenseReport($request->all());

        return $this->sendResponse($data, __('messages.flash.income_and_expense_retrieved'));
    }

    /**
     * @return Application|Factory|\Illuminate\Contracts\View\View
     */
    public function featureAvailability(): View
    {
        return view('menu_feature.index');
    }

    public function incomeChart(Request $request): JsonResponse
    {
        $input = $request->all();
        $startDate = str_replace('/', '-', $input['start_date']);
        $endDate = str_replace('/', '-', $input['end_date']);
        $formatStartDate = Carbon::parse($startDate)->format('Y-m-d');
        $formatEndDate = Carbon::parse($endDate)->format('Y-m-d');

        $data = $this->dashboardRepository->totalFilterDay($formatStartDate, $formatEndDate);

        return $this->sendResponse($data, __('messages.flash.income_report_generate'));
    }
}
