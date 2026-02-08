<?php

namespace Tests\Feature;

use Tests\TestCase;

class SmokeTest extends TestCase
{
    /** @test */
    public function root_redirects_to_login_or_attendance()
    {
        $response = $this->get('/');
        $response->assertStatus(302);
    }
}
