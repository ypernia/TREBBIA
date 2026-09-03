<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\BusinessSetupController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PasswordResetLinkController;
use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
})->name('home');

Route::get('/reservar/{business:slug}', [PublicBookingController::class, 'show'])->name('public-booking.show');
Route::post('/reservar/{business:slug}', [PublicBookingController::class, 'store'])->name('public-booking.store');
Route::get('/reservar/{business:slug}/confirmacion/{appointment}', [PublicBookingController::class, 'confirmation'])->name('public-booking.confirmation');

Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/empresa/nueva', [BusinessSetupController::class, 'create'])->name('business.create');
    Route::post('/empresa/nueva', [BusinessSetupController::class, 'store'])->name('business.store');

    Route::middleware('business')->group(function () {
        Route::get('/onboarding/{step?}', [OnboardingController::class, 'show'])->name('onboarding.show');
        Route::post('/onboarding/{step}', [OnboardingController::class, 'store'])->name('onboarding.store');
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::resource('agenda', AppointmentController::class)->parameters(['agenda' => 'appointment'])->except(['show']);
        Route::resource('servicios', ServiceController::class)->except(['show']);
        Route::resource('profesionales', ProfessionalController::class)->except(['show']);
        Route::resource('clientes', ClientController::class);
        Route::post('/recursos/sugeridos', [ResourceController::class, 'storeSuggestions'])->name('recursos.suggestions.store');
        Route::resource('recursos', ResourceController::class)->except(['show']);
        Route::get('/configuracion', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/configuracion/negocio', [SettingsController::class, 'updateBusiness'])->name('settings.business.update');
        Route::put('/configuracion/preferencias', [SettingsController::class, 'updatePreferences'])->name('settings.preferences.update');
        Route::post('/configuracion/sedes', [SettingsController::class, 'storeBranch'])->name('settings.branches.store');
        Route::put('/configuracion/sedes/{branch}', [SettingsController::class, 'updateBranch'])->name('settings.branches.update');
        Route::get('/configuracion/horarios', [ScheduleController::class, 'edit'])->name('schedules.edit');
        Route::put('/configuracion/horarios', [ScheduleController::class, 'update'])->name('schedules.update');
        Route::get('/modulos/automatizaciones', [AutomationController::class, 'index'])->name('automations.index');
        Route::post('/modulos/automatizaciones/plantillas', [AutomationController::class, 'storeTemplate'])->name('automations.templates.store');
        Route::put('/modulos/automatizaciones/plantillas/{template}', [AutomationController::class, 'updateTemplate'])->name('automations.templates.update');
        Route::post('/agenda/{appointment}/recordatorios', [AutomationController::class, 'scheduleReminder'])->name('automations.reminders.schedule');
        Route::patch('/recordatorios/{reminder}/enviado', [AutomationController::class, 'markReminderSent'])->name('automations.reminders.sent');
        Route::patch('/recordatorios/{reminder}/omitido', [AutomationController::class, 'skipReminder'])->name('automations.reminders.skip');
        Route::get('/modulos/reportes', ReportController::class)->name('reports.index');
        Route::get('/modulos/membresia', [MembershipController::class, 'index'])->name('membership.index');
        Route::put('/modulos/membresia', [MembershipController::class, 'update'])->name('membership.update');
        Route::get('/modulos/{module}', ModuleController::class)->name('modules.show');
    });
});
