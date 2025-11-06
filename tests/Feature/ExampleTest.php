<?php

// Default example test file kept minimal; directory-level TestCase is configured in tests/Pest.php

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
