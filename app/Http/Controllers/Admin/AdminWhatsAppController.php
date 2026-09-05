<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppActivationRequest;
use Illuminate\View\View;

class AdminWhatsAppController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.whatsapp.index', [
            'requests' => WhatsAppActivationRequest::with('business.owner')->latest()->paginate(15),
        ]);
    }
}
