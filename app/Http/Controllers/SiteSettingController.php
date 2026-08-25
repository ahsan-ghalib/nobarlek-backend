<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Symfony\Component\HttpFoundation\Response;

class SiteSettingController extends Controller
{
    public function __invoke()
    {
        return $this->jsonResponse(SiteSetting::instance()->toPublicArray(), Response::HTTP_OK);
    }
}
