<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify-certificate/{token}', function (string $token) {
    $certificate = app(
        \App\Domain\Tenant\Services\CertificateService::class
    )->verifyByToken($token);

    return view('certificates.verify', [
        'certificate' => $certificate,
    ]);
})->name('certificates.verify.public');

Route::post(
    '/webhooks/gateway/{provider}',
    [\App\Http\Controllers\GatewayWebhookController::class, 'handle']
)->name('webhooks.gateway');