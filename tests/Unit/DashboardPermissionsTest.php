<?php

namespace Tests\Unit;

use App\Support\DashboardPermissions;
use Tests\TestCase;

class DashboardPermissionsTest extends TestCase
{
    public function test_permission_catalog_contains_user_and_module_permissions(): void
    {
        $names = DashboardPermissions::names();

        $this->assertContains('users.view', $names);
        $this->assertContains('users.create', $names);
        $this->assertContains('news.view', $names);
        $this->assertContains('seo.home', $names);
        $this->assertSame('admin', DashboardPermissions::adminRole());
        $this->assertNotEmpty(DashboardPermissions::groups());
    }
}
