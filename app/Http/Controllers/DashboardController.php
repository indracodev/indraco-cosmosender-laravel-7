<?php

namespace App\Http\Controllers;

use App\Models\Brand;

class DashboardController extends Controller
{
    public function index()
    {
        $brands = Brand::with([
            'service.server',
            'activeBlastRules.emailHost.server',
            'activeBlastRules.activeDetails',
            'activeBlastRules.activeRecipients',
        ])
            ->withCount([
                'activeCustomerEmails as subscriber_count',
                'contents as content_count' => function ($q) {
                    $q->where('flag', 'Y');
                },
            ])
            ->where('flag', 'Y')
            ->orderBy('id', 'asc')
            ->get();

        return view('dashboard.index', compact('brands'));
    }
}

