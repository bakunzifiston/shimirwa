<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Client\StoreClientRequest;
use App\Http\Requests\Admin\Client\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $role = $request->string('role')->toString();

        $clients = Client::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('supplier_code', 'like', "%{$search}%");
                });
            })
            ->when(in_array($role, ['client', 'supplier'], true), fn ($q) => $q->where('role', $role))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $totalClients   = Client::where('role', 'client')->count();
        $totalSuppliers = Client::where('role', 'supplier')->count();
        $thisMonth      = Client::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $lastMonth      = Client::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();
        $delta          = $lastMonth > 0 ? sprintf('%+d%%', round(($thisMonth - $lastMonth) / $lastMonth * 100)) : ($thisMonth > 0 ? '+100%' : '0%');
        $pageStats = [
            ['label' => 'Total',      'value' => Client::count(),    'icon' => 'users', 'color' => 'blue',   'delta' => null],
            ['label' => 'Clients',    'value' => $totalClients,      'icon' => 'users', 'color' => 'green',  'delta' => null],
            ['label' => 'Suppliers',  'value' => $totalSuppliers,    'icon' => 'box', 'color' => 'amber',  'delta' => null],
            ['label' => 'This month', 'value' => $thisMonth.' new',  'icon' => 'trend', 'color' => 'purple', 'delta' => $delta],
        ];

        return view('admin.clients.index', compact('clients', 'search', 'role', 'pageStats'));
    }

    public function create(): View
    {
        return view('admin.clients.create', ['client' => new Client]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        Client::create($request->validated());

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Record created successfully.');
    }

    public function show(Client $client): View
    {
        return view('admin.clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Record updated successfully.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Record deleted successfully.');
    }
}
