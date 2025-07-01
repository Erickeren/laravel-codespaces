<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShiftController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [ShiftController::class, 'index'])->name('shifts.index');
Route::post('/people', [ShiftController::class, 'storePeople'])->name('people.store');
Route::post('/schedule/generate', [ShiftController::class, 'generateSchedule'])->name('schedule.generate');
Route::get('/schedule/export', [ShiftController::class, 'export'])->name('schedule.export');
