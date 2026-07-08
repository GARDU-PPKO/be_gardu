<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBookingSessionController extends Controller
{
    public function index(): View
    {
        return view('admin.booking-sessions.index', ['sessions' => BookingSession::all()]);
    }

    public function create(): View
    {
        return view('admin.booking-sessions.form', ['session' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => 'required|string|max:20',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'is_active' => 'boolean',
        ]);

        BookingSession::create($data);

        return redirect()->route('admin.booking-sessions.index')->with('success', 'Sesi booking berhasil ditambahkan');
    }

    public function edit($id): View
    {
        return view('admin.booking-sessions.form', ['session' => BookingSession::findOrFail($id)]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $session = BookingSession::findOrFail($id);
        $data = $request->validate([
            'nama' => 'required|string|max:20',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'is_active' => 'boolean',
        ]);

        $session->update($data);
        return redirect()->route('admin.booking-sessions.index')->with('success', 'Sesi booking berhasil diupdate');
    }

    public function destroy($id): RedirectResponse
    {
        BookingSession::findOrFail($id)->delete();
        return redirect()->route('admin.booking-sessions.index')->with('success', 'Sesi booking berhasil dihapus');
    }
}
