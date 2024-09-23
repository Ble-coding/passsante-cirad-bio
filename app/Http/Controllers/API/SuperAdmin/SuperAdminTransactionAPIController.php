<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class SuperAdminTransactionAPIController extends AppBaseController
{
    public function showTransaction()
    {
        $transactions = Transaction::whereHas('user', function ($q) {
            $q->where('department_id', 1);
        })->with(['transactionSubscription.subscriptionPlan', 'user'])->orderBy('id', 'desc')->get();

        $data = [];
        foreach ($transactions as $transaction) {
            $data[] = $transaction->prepareTransaction();
        }

        if (getLoggedInUser()->hasRole('Admin')) {
            $transactions->where('user_id', '=', getLoggedInUserId());
            foreach ($transactions as $transaction) {
                $data[] = $transaction->prepareTransaction();
            }
        }

        return $this->sendResponse($data, 'Transaction Retrieved Successfully');
    }

    public function filter(Request $request)
    {
       $status = $request->get('status');
       $transactionsQuery = Transaction::whereHas('user', function ($q) {
                $q->where('department_id', 1);
            })->with(['transactionSubscription.subscriptionPlan', 'user']);
        $data = [];

        if($status == 'all') {
            $transactions = $transactionsQuery->orderBy('id', 'desc')->get();

            foreach ($transactions as $transaction) {
                $data[] = $transaction->prepareTransaction();
            }
            return $this->sendResponse($data, 'Transaction Retrieved Successfully');
        }elseif($status == 'paystack') {
            $transactions = $transactionsQuery->where('payment_type',6)->orderBy('id', 'desc')->get();

            foreach ($transactions as $transaction) {
                $data[] = $transaction->prepareTransaction();
            }
            return $this->sendResponse($data, 'Transaction Retrieved Successfully');
        }elseif($status == 'paytm') {
            $transactions = $transactionsQuery->where('payment_type',5)->orderBy('id', 'desc')->get();

            foreach ($transactions as $transaction) {
                $data[] = $transaction->prepareTransaction();
            }
            return $this->sendResponse($data, 'Transaction Retrieved Successfully');
        }elseif($status == 'manual') {
            $transactions = $transactionsQuery->where('payment_type',4)->orderBy('id', 'desc')->get();

            foreach ($transactions as $transaction) {
                $data[] = $transaction->prepareTransaction();
            }
            return $this->sendResponse($data, 'Transaction Retrieved Successfully');
        }elseif($status == 'razorpay') {
            $transactions = $transactionsQuery->where('payment_type',3)->orderBy('id', 'desc')->get();

            foreach ($transactions as $transaction) {
                $data[] = $transaction->prepareTransaction();
            }
            return $this->sendResponse($data, 'Transaction Retrieved Successfully');
        }elseif($status == 'paypal') {
            $transactions = $transactionsQuery->where('payment_type',2)->orderBy('id', 'desc')->get();

            foreach ($transactions as $transaction) {
                $data[] = $transaction->prepareTransaction();
            }
            return $this->sendResponse($data, 'Transaction Retrieved Successfully');
        }elseif($status == 'stripe') {
            $transactions = $transactionsQuery->where('payment_type',1)->orderBy('id', 'desc')->get();

            foreach ($transactions as $transaction) {
                $data[] = $transaction->prepareTransaction();
            }
            return $this->sendResponse($data, 'Transaction Retrieved Successfully');
        }else{
            return $this->sendError('Transaction not found');
        }

    }
}
