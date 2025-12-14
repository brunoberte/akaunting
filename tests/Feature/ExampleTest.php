<?php

it('home page should redirect to dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/dashboard');
});
