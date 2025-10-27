<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Http\Middleware\VerifyCsrfToken;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト実行時に CSRF ミドルウェアを無効化（フォーム系の 419 を防止）
        $this->withoutMiddleware(VerifyCsrfToken::class);

        // もし必要ならセッションドライバを array に（高速化・状態を共有しない）
        // config(['session.driver' => 'array']);
    }
}
