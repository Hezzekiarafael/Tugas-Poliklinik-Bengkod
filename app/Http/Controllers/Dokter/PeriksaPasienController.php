<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create($id)
    {
        $obats = Obat::all();
        return view('dokter.periksa-pasien.create', compact('obats', 'id'));
    }

   public function store(Request $request)
    {
        $request->validate([
            'obat_json' => 'required',
            'catatan' => 'nullable|string',
            'biaya_periksa' => 'required|integer',
        ]);

        $obatIds = json_decode($request->obat_json, true);

        // --- 1. HANDLING STOK HABIS (Validasi & Error Handling) ---
        if (!empty($obatIds)) {
            foreach ($obatIds as $idObat) {
                $obat = Obat::find($idObat);
                if ($obat && $obat->stok < 1) {
                    // Jika ada obat yang habis, kembalikan dokter ke halaman sebelumnya dengan error
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Gagal! Stok obat '{$obat->nama_obat}' sudah habis. Silakan pilih obat lain.");
                }
            }
        }
        // -----------------------------------------------------------

        $periksa = Periksa::create([
            'id_daftar_poli' => $request->id_daftar_poli,
            'tgl_periksa' => now(),
            'catatan' => $request->catatan,
            'biaya_periksa' => $request->biaya_periksa + 150000,
        ]);

        foreach ($obatIds as $idObat) {
            DetailPeriksa::create([
                'id_periksa' => $periksa->id,
                'id_obat' => $idObat,
            ]);

            // --- 2. SISTEM OTOMATIS MENGURANGI STOK ---
            $obat = Obat::find($idObat);
            if ($obat) {
                $obat->decrement('stok'); // Mengurangi kolom stok sebanyak 1
            }
            // ------------------------------------------
        }

        return redirect()->route('periksa-pasien.index')
            ->with('success', 'Data periksa dan resep obat berhasil disimpan.');
    }

}