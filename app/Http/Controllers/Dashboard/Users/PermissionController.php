<?php

namespace App\Http\Controllers\Dashboard\Users;

use App\Http\Controllers\Controller;
use App\Support\DashboardPermissions;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.view|users.create|users.update');
    }

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => DashboardPermissions::groups(),
            'message' => 'Successfully retrieved permissions',
        ]);
    }
}
