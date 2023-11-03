<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function insertData() {
    DB::statement("INSERT INTO courses (name, created_at, updated_at) VALUES (?, NOW(), NOW())", ['Algebra']);
    DB::statement("INSERT INTO domain_categories (name, created_at, updated_at) VALUES (?, NOW(), NOW())", ['Mathematics']);
    DB::statement("INSERT INTO users (username, password, created_at, updated_at) VALUES (?, ?, NOW(), NOW())", ['john_doe', bcrypt('password123')]);
    DB::statement("INSERT INTO exercises (course_id, category_id, name, points, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())", [1, 1, 'Solving Linear Equations', 10]);

    DB::statement("INSERT INTO sessions (user_id, created_at, updated_at) VALUES (?, NOW(), NOW())", [1]);
    DB::statement("INSERT INTO scores (session_id, exercise_id, score, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())", [1, 1, 8]);
}

it('returns the sessions history', function () {
    insertData();

    $user = DB::select('SELECT * FROM users WHERE id = ?', [1])[0];
    $session = DB::select('SELECT * FROM sessions WHERE user_id = ?', [$user->id])[0];
    $score = DB::select('SELECT * FROM scores WHERE session_id = ?', [$session->id])[0];

    $response = $this->get('api/sessions-history');

    $response->assertJson([
        'history' => []
    ]);

    expect($response['history'])->toBeArray();
    expect($response['history'][0])->toHaveKeys(['score', 'date']);

    expect($response['history'][0]['date'])->toBeInt();
    expect($response['history'][0]['score'])->toBeInt();
    expect($response['history'][0][1]['score'])->toBe($score);
});
