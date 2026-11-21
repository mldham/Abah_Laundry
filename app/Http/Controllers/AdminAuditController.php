<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\View\View;

class AdminAuditController extends Controller
{
    public function index(): View
    {
        $log = LogAktivitas::with('pelaku')->orderByDesc('id')->simplePaginate(25);

        return view('admin.audit', compact('log'));
    }
}
