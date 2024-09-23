<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateIpdPaymentRequest;
use App\Http\Requests\UpdateIpdPaymentRequest;
use App\Models\IpdPayment;
use App\Repositories\IpdPaymentRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class IpdPaymentController extends AppBaseController
{
    /** @var IpdPaymentRepository */
    private $ipdPaymentRepository;

    public function __construct(IpdPaymentRepository $ipdPaymentRepo)
    {
        $this->ipdPaymentRepository = $ipdPaymentRepo;
    }

    /**
     * Display a listing of the IpdPayment.
     *
     *
     * @throws Exception
     */
    public function index(Request $request): Response
    {
    }

    /**
     * Store a newly created IpdPayment in storage.
     */
    public function store(CreateIpdPaymentRequest $request): JsonResponse
    {
        $input = $request->all();

        $this->ipdPaymentRepository->store($input);

        return $this->sendSuccess(__('messages.flash.IPD_payment_saved'));
    }

    /**
     * Show the form for editing the specified Ipd Payment.
     */
    public function edit(IpdPayment $ipdPayment): JsonResponse
    {
        if (! canAccessRecord(IpdPayment::class, $ipdPayment->id)) {
            return $this->sendError(__('messages.flash.ipd_payment_not_found'));
        }

        return $this->sendResponse($ipdPayment, __('messages.flash.IPD_payment_retrieved'));
    }

    /**
     * Update the specified Ipd Payment in storage.
     */
    public function update(IpdPayment $ipdPayment, UpdateIpdPaymentRequest $request): JsonResponse
    {
        $this->ipdPaymentRepository->updateIpdPayment($request->all(), $ipdPayment->id);

        return $this->sendSuccess(__('messages.flash.IPD_payment_updated'));
    }

    /**
     * Remove the specified IpdPayment from storage.
     *
     *
     * @throws Exception
     */
    public function destroy(IpdPayment $ipdPayment): JsonResponse
    {
        if (! canAccessRecord(IpdPayment::class, $ipdPayment->id)) {
            return $this->sendError(__('messages.flash.ipd_payment_not_found'));
        }

        $this->ipdPaymentRepository->deleteIpdPayment($ipdPayment->id);

        return $this->sendSuccess(__('messages.flash.IPD_payment_deleted'));
    }

    public function downloadMedia(IpdPayment $ipdPayment): Media
    {
        $media = $ipdPayment->getMedia(IpdPayment::IPD_PAYMENT_PATH)->first();
        ob_end_clean();
        if ($media != null) {
            $media = $media->id;
            $mediaItem = Media::findOrFail($media);

            return $mediaItem;
        }

        return '';
    }
}
