<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProxyHttpsTest extends TestCase
{
    public function test_detras_del_proxy_https_los_enlaces_salen_con_https(): void
    {
        $this->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'casa-blanca.example', 'X-Forwarded-Port' => '443'])
            ->get('/login')
            ->assertOk()
            ->assertSee('action="https://casa-blanca.example/login"', false);
    }
}
