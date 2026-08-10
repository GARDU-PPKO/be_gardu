<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingSession;
use App\Models\PaketWisata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBookingSessionController extends Controller
{
    public function index(): View
    {
        return view('admin.booking-sessions.index', [
            'sessions' => BookingSession::with('paket:id,nama')->orderBy('paket_wisata_id')->orderBy('sesi')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.booking-sessions.form', ['session' => null, 'packages' => PaketWisata::all()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['created_by'] = $request->user()->id;

        BookingSession::create($data);

        return redirect()->route('admin.booking-sessions.index')->with('success', 'Template sesi berhasil ditambahkan');
    }

    public function edit($id): View
    {
        return view('admin.booking-sessions.form', [
            'session'  => BookingSession::findOrFail($id),
            'packages' => PaketWisata::all(),
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $session = BookingSession::findOrFail($id);
        $session->update($this->validatedData($request));

        return redirect()->route('admin.booking-sessions.index')->with('success', 'Template sesi berhasil diupdate');
    }

    public function destroy($id): RedirectResponse
    {
        BookingSession::findOrFail($id)->delete();

        return redirect()->route('admin.booking-sessions.index')->with('success', 'Template sesi berhasil dihapus');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'paket_wisata_id' => 'required|exists:paket_wisata,id',
            'sesi'            => 'required|in:Pagi,Siang,Sore',
            'jam_mulai'       => 'nullable|date_format:H:i',
            'jam_selesai'     => 'nullable|date_format:H:i|after:jam_mulai',
            'kuota'           => 'required|integer|min:1',
            'is_active'       => 'boolean',
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
