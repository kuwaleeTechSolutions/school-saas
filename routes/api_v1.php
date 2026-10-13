<?php
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\ParentController;
use App\Http\Controllers\Api\V1\StaffStudentController;
use App\Http\Controllers\Api\V1\TeacherController;
use App\Http\Middleware\ApiAuthenticate;
use App\Http\Middleware\ApiRequireAbility;
use Illuminate\Support\Facades\Route;
/* Load from routes/api.php with: require __DIR__.'/api_v1.php';  (and delete the empty `v1` stub group left from Stage 1). URLs: /api/v1/... */
Route::prefix('v1')->group(function () {
    Route::post('auth/token', [AuthController::class, 'token'])->middleware('throttle:20,1');
    Route::middleware([ApiAuthenticate::class, 'throttle:api-v1'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', MeController::class);
        Route::get('students', [StaffStudentController::class, 'index'])->middleware(ApiRequireAbility::class.':staff');
        Route::get('parent/children', [ParentController::class, 'children'])->middleware(ApiRequireAbility::class.':parent');
        Route::get('teacher/classes', [TeacherController::class, 'classes'])->middleware(ApiRequireAbility::class.':teacher');
    });
});
