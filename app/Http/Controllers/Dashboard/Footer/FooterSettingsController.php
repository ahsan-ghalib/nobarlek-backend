<?php

namespace App\Http\Controllers\Dashboard\Footer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateFooterSettingsRequest;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FooterSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:footer.settings');
    }

    public function show()
    {
        return response()->json(SiteSetting::instance()->toPublicArray());
    }

    public function update(UpdateFooterSettingsRequest $request)
    {
        $settings = SiteSetting::instance();
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            $validated['logo'] = Storage::disk('public')->put('/site-settings', $request->file('logo'));
        } else {
            unset($validated['logo']);
        }

        if ($request->hasFile('favicon')) {
            $validated['favicon'] = Storage::disk('public')->put('/site-settings', $request->file('favicon'));
        } else {
            unset($validated['favicon']);
        }

        $settings->update($validated);

        if (!$settings->wasChanged()) {
            return response()->json([
                'data' => $settings->fresh()->toPublicArray(),
                'message' => 'Footer settings not updated',
            ]);
        }

        return response()->json([
            'data' => $settings->fresh()->toPublicArray(),
            'message' => 'Successfully footer settings updated',
        ]);
    }
}
