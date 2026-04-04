<?php

use App\Domain\Ticketing\Models\Ticket;
use App\Presentation\Livewire\Auth\Login;
use App\Presentation\Livewire\Branch\BranchManager;
use App\Presentation\Livewire\Branch\UserManager;
use App\Presentation\Livewire\Customer\CustomerManager;
use App\Presentation\Livewire\Customer\CustomerShow;
use App\Presentation\Livewire\Finance\FinanceDashboard;
use App\Presentation\Livewire\Inventory\ProductManager;
use App\Presentation\Livewire\Inventory\WarehouseManager;
use App\Presentation\Livewire\Settings\ChecklistTemplates;
use App\Presentation\Livewire\Settings\Dashboard;
use App\Presentation\Livewire\Settings\DeviceDictionary;
use App\Presentation\Livewire\Ticketing\ClientPortal;
use App\Presentation\Livewire\Ticketing\TicketManager;
use App\Presentation\Livewire\Ticketing\TicketShow;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', Login::class)->name('login');
Route::get('/status/{token}', ClientPortal::class)->name('client.portal');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect('/login');
})->name('logout');

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }
    if (auth()->user()->hasRole('Storekeeper')) {
        return redirect()->route('inventory.products');
    }

    return redirect()->route('dashboard');
});
Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:Admin|Branch Manager'])->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/branches', BranchManager::class)->name('branches.index');
        Route::get('/users', UserManager::class)->name('users.index');
        Route::get('/users/{user}', \App\Presentation\Livewire\Branch\UserShow::class)->name('users.show');
        Route::get('/customers', CustomerManager::class)->name('customers.index');
        Route::get('/finance', FinanceDashboard::class)->name('finance.index');
        Route::get('/settings/devices', DeviceDictionary::class)->name('settings.devices');
        Route::get('/settings/checklists', ChecklistTemplates::class)->name('settings.checklists');
        Route::get('/customers/{customer}', CustomerShow::class)->name('customers.show');
    });
    Route::middleware(['role:Admin|Storekeeper'])->group(function () {
        Route::get('/inventory', WarehouseManager::class)->name('inventory.index');
        Route::get('/inventory/products', ProductManager::class)->name('inventory.products');
    });
    Route::middleware(['role:Admin|Branch Manager|Technician'])->group(function () {
        Route::get('/tickets', TicketManager::class)->name('tickets.index');
        Route::get('/tickets/{ticket:ulid}', TicketShow::class)->name('tickets.show');

        Route::get('/tickets/{ticket:ulid}/invoice', function (Ticket $ticket) {
            $ticket->load(['customer', 'branch', 'usedParts.inventoryItem.product']);
            $pdf = Pdf::loadView('pdf.invoice', compact('ticket'));

            return $pdf->stream('Акт_выполненных_работ_'.$ticket->ulid.'.pdf');
        })->name('tickets.invoice');
    });
});
