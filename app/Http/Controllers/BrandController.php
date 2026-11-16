<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::with('service.server')->orderBy('id', 'asc')->get();
        $services = Service::with('server')->where('flag', 'Y')->orderBy('id', 'asc')->get();

        return view('settings.brands', compact('brands', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:100',
            'email_alias'         => 'nullable|string|max:255',
            'domain'              => 'required|string|max:100',
            'aseet_namespace'     => 'required|string|max:100',
            'service_id'          => 'required|integer',
            'actual_path'         => 'required|string|max:100',
            'content_domain'      => 'required|string|max:255',
            'blast_limit'         => 'nullable|integer|min:0',
            'blast_hour_interval' => 'nullable|integer|min:0',
        ]);

        Brand::create([
            'name'                => $validated['name'],
            'email_alias'         => $validated['email_alias'] ?? $validated['name'],
            'slug'                => Str::slug($validated['name']),
            'domain'              => $validated['domain'],
            'aseet_namespace'     => $validated['aseet_namespace'],
            'service_id'          => $validated['service_id'],
            'actual_path'         => $validated['actual_path'],
            'content_domain'      => $validated['content_domain'],
            'blast_limit'         => (int) ($validated['blast_limit'] ?? 0),
            'blast_hour_interval' => (int) ($validated['blast_hour_interval'] ?? 0),
            'flag'                => 'Y',
        ]);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil menambahkan Brand "' . $validated['name'] . '".',
        ]);
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:100',
            'email_alias'         => 'nullable|string|max:255',
            'domain'              => 'required|string|max:100',
            'aseet_namespace'     => 'required|string|max:100',
            'service_id'          => 'required|integer',
            'actual_path'         => 'required|string|max:100',
            'content_domain'      => 'required|string|max:255',
            'blast_limit'         => 'nullable|integer|min:0',
            'blast_hour_interval' => 'nullable|integer|min:0',
            'flag'                => 'required|in:Y,N',
        ]);

        $brand->update([
            'name'                => $validated['name'],
            'email_alias'         => $validated['email_alias'] ?? $brand->email_alias,
            'domain'              => $validated['domain'],
            'aseet_namespace'     => $validated['aseet_namespace'],
            'service_id'          => $validated['service_id'],
            'actual_path'         => $validated['actual_path'],
            'content_domain'      => $validated['content_domain'],
            'blast_limit'         => (int) ($validated['blast_limit'] ?? 0),
            'blast_hour_interval' => (int) ($validated['blast_hour_interval'] ?? 0),
            'flag'                => $validated['flag'],
        ]);

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil memperbarui Brand "' . $brand->name . '".',
        ]);
    }
}

