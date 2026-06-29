<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingSession;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBookingSessionController extends Controller
{
    public function index(): View
    {
        return view('admin.booking-sessions.index', ['sessions' => BookingSession::with('package:id,nama')->paginate(25)]);
    }

    public function create(): View
    {
        return view('admin.booking-sessions.form', ['session' => null, 'packages' => TourPackage::all()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'package_id' => 'required|exists:tour_packages,id',
            'tanggal' => 'required|date',
            'sesi' => 'required|in:Pagi,Siang,Sore',
            'kuota' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $data['created_by'] = $request->user()->id;
        BookingSession::create($data);

        return redirect()->route('admin.booking-sessions.index')->with('success', 'Sesi booking berhasil ditambahkan');
    }

    public function edit($id): View
    {
        return view('admin.booking-sessions.form', [
            'session' => BookingSession::findOrFail($id),
            'packages' => TourPackage::all(),
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $session = BookingSession::findOrFail($id);
        $data = $request->validate([
            'package_id' => 'required|exists:tour_packages,id',
            'tanggal' => 'required|date',
            'sesi' => 'required|in:Pagi,Siang,Sore',
            'kuota' => 'required|integer|min:1',
            'terisi' => 'required|integer|min:0',
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
