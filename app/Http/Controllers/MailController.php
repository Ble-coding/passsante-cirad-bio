<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMailRequest;
use App\Repositories\MailRepository;
use Flash;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

class MailController extends Controller
{
    /** @var MailRepository */
    private $mailRepository;

    public function __construct(MailRepository $mailRepo)
    {
        $this->mailRepository = $mailRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function index(): View
    {
        return view('mail.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return RedirectResponse|Redirector
     */
    public function store(CreateMailRequest $request): RedirectResponse
    {
        $input = $request->all();
        $this->mailRepository->store($input);
        Flash::success(__('messages.flash.mail_sent'));

        return redirect(route('mail'));
    }
}
