<?php

test('the home page redirects to the pesanan list', function () {
    $response = $this->get('/');

    $response
        ->assertRedirectToRoute('pesanan.index');
});
