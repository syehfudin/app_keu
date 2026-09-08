<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonaturController;
use App\Http\Controllers\KorelController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PekerjaanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RehaController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SetoranController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/home', [DashboardController::class, 'index'])->name('home');
Route::prefix('dashboard')->group(function () {
    // route::get('/', [DashboardController::class, 'index'])->name('home');
    route::get('/data', [DashboardController::class, 'indexData'])->name('dashboard.data');
    route::post('/data_chart', [DashboardController::class, 'indexDataChart'])->name('dashboard.dataChart');
});

Route::group(['middleware' => ['auth']], function () {
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/{id}', [RoleController::class, 'show'])->name('roles.show');
        // Route::get('/{id}/indexData', [RoleController::class, 'indexData'])->name('roles.indexData');
        Route::get('/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::patch('/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
    // Route::get('bebas/indexData', [RoleController::class, 'indexData'])->name('roles.indexData');
    // Route::resource('roles', RoleController::class);

    Route::resource('users', UserController::class);
    Route::resource('products', ProductController::class);

    Route::prefix('pegawai')->group(function () {
        Route::get('/', [PegawaiController::class, 'index'])->name('pegawai.index');
        Route::get('/create', [PegawaiController::class, 'create'])->name('pegawai.create');
        Route::post('/', [PegawaiController::class, 'store'])->name('pegawai.store');
        Route::get('/show/{id}', [PegawaiController::class, 'show'])->name('pegawai.show');
        Route::get('/edit//{id}', [PegawaiController::class, 'edit'])->name('pegawai.edit');
        Route::post('/update/{id}', [PegawaiController::class, 'update'])->name('pegawai.update');
        Route::delete('/{id}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy');
        Route::get('/index_data', [PegawaiController::class, 'indexData'])->name('pegawai.index_data');
    });

    Route::prefix('donatur')->group(function () {
        Route::get('/', [DonaturController::class, 'index'])->name('donatur.index');
        Route::get('/create', [DonaturController::class, 'create'])->name('donatur.create');
        Route::post('/', [DonaturController::class, 'store'])->name('donatur.store');
        Route::get('/show/{id}', [DonaturController::class, 'show'])->name('donatur.show');
        Route::get('/edit//{id}', [DonaturController::class, 'edit'])->name('donatur.edit');
        Route::post('/update/{id}', [DonaturController::class, 'update'])->name('donatur.update');
        Route::delete('/{id}', [DonaturController::class, 'destroy'])->name('donatur.destroy');
        Route::get('/index_data', [DonaturController::class, 'indexData'])->name('donatur.index_data');
    });

    Route::prefix('pekerjaan')->group(function () {
        Route::get('/', [PekerjaanController::class, 'index'])->name('pekerjaan.index');
        Route::get('/create', [PekerjaanController::class, 'create'])->name('pekerjaan.create');
        Route::post('/', [PekerjaanController::class, 'store'])->name('pekerjaan.store');
        Route::get('/show/{id}', [PekerjaanController::class, 'show'])->name('pekerjaan.show');
        Route::get('/edit//{id}', [PekerjaanController::class, 'edit'])->name('pekerjaan.edit');
        Route::post('/update/{id}', [PekerjaanController::class, 'update'])->name('pekerjaan.update');
        Route::delete('/{id}', [PekerjaanController::class, 'destroy'])->name('pekerjaan.destroy');
        Route::get('/index_data', [PekerjaanController::class, 'indexData'])->name('pekerjaan.index_data');
    });

    Route::prefix('program')->group(function () {
        Route::get('/', [ProgramController::class, 'index'])->name('program.index');
        Route::get('/create', [ProgramController::class, 'create'])->name('program.create');
        Route::post('/', [ProgramController::class, 'store'])->name('program.store');
        Route::get('/show/{id}', [ProgramController::class, 'show'])->name('program.show');
        Route::get('/edit//{id}', [ProgramController::class, 'edit'])->name('program.edit');
        Route::post('/update/{id}', [ProgramController::class, 'update'])->name('program.update');
        Route::delete('/{id}', [ProgramController::class, 'destroy'])->name('program.destroy');
        Route::get('/index_data', [ProgramController::class, 'indexData'])->name('program.index_data');
    });

    Route::prefix('transaksi')->group(function () {
        Route::get('/', [TransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/create', [TransaksiController::class, 'create'])->name('transaksi.create');
        Route::post('/', [TransaksiController::class, 'store'])->name('transaksi.store');
        Route::get('/show/{id}', [TransaksiController::class, 'show'])->name('transaksi.show');
        Route::get('/edit//{id}', [TransaksiController::class, 'edit'])->name('transaksi.edit');
        Route::post('/update/{id}', [TransaksiController::class, 'update'])->name('transaksi.update');
        Route::delete('/{id}', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');
        Route::get('/index_data', [TransaksiController::class, 'indexData'])->name('transaksi.index_data');
        Route::get('/print/{id}', [TransaksiController::class, 'importPdf'])->name('transaksi.importPdf');
    });

    Route::prefix('setoran')->group(function () {
        Route::get('/', [SetoranController::class, 'index'])->name('setoran.index');
        Route::get('/create', [SetoranController::class, 'create'])->name('setoran.create');
        Route::post('/', [SetoranController::class, 'store'])->name('setoran.store');
        Route::get('/show/{id}', [SetoranController::class, 'show'])->name('setoran.show');
        Route::get('/edit//{id}', [SetoranController::class, 'edit'])->name('setoran.edit');
        Route::post('/update/{id}', [SetoranController::class, 'update'])->name('setoran.update');
        Route::delete('/{id}', [SetoranController::class, 'destroy'])->name('setoran.destroy');
        Route::get('/index_data', [SetoranController::class, 'indexData'])->name('setoran.index_data');
    });

    Route::prefix('korel')->group(function () {
        Route::get('/', [KorelController::class, 'index'])->name('korel.index');
        Route::get('/create', [KorelController::class, 'create'])->name('korel.create');
        Route::post('/', [KorelController::class, 'store'])->name('korel.store');
        Route::get('/show/{id}', [KorelController::class, 'show'])->name('korel.show');
        Route::get('/edit//{id}', [KorelController::class, 'edit'])->name('korel.edit');
        Route::post('/update/{id}', [KorelController::class, 'update'])->name('korel.update');
        Route::delete('/{id}', [KorelController::class, 'destroy'])->name('korel.destroy');
        Route::get('/index_data', [KorelController::class, 'indexData'])->name('korel.index_data');
    });

    Route::prefix('reha')->group(function () {
        Route::get('/', [RehaController::class, 'index'])->name('reha.index');
        Route::get('/create', [RehaController::class, 'create'])->name('reha.create');
        Route::post('/', [RehaController::class, 'store'])->name('reha.store');
        Route::get('/show/{id}', [RehaController::class, 'show'])->name('reha.show');
        Route::get('/edit//{id}', [RehaController::class, 'edit'])->name('reha.edit');
        Route::post('/update/{id}', [RehaController::class, 'update'])->name('reha.update');
        Route::delete('/{id}', [RehaController::class, 'destroy'])->name('reha.destroy');
        Route::get('/index_data', [RehaController::class, 'indexData'])->name('reha.index_data');
    });
});
