<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthPagesTest extends TestCase
{
    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_register_page_loads(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_guest_is_redirected_from_contributor_dashboard(): void
    {
        $this->get('/my/articles')->assertRedirect('/login');
    }
}