<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_a_missing_page_shows_the_custom_404_view(): void
    {
        $response = $this->get('/esta-ruta-no-existe-de-verdad');

        $response
            ->assertNotFound()
            ->assertSee('Per más que busqué, nun salió');
    }
}
