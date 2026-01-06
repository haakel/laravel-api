<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\DataController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\SongController;

// این Route اول قرار داره
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route مربوط به data کامنت شده
// Route::get('/data', [DataController::class]);
// پیشوند v1
Route::prefix('v1')->group(function () {
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    
    // این Route‌ها برای songs
    Route::get('/songs', [SongController::class, 'index']);
    Route::post('/songs', [SongController::class, 'store']);
});


// // اضافه کردن Route fallback برای API
// Route::fallback(function () {
//     return response()->json([
//         'message' => 'API endpoint not found. Check your request method and URL.',
//         'available_endpoints' => [
//             'GET /api/v1/songs',
//             'POST /api/v1/songs',
//             'POST /api/v1/categories',
//             'PUT /api/v1/categories/{id}',
//             'DELETE /api/v1/categories/{id}'
//         ]
//     ], 404);
// });