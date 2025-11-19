<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    // GET /laporan - Melihat daftar laporan
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang dan KepalaDivisi yang bisa melihat laporan
            if (!$user->hasRole(['AdminGudang', 'KepalaDivisi'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk melihat laporan'
                ], 403);
            }

            // Simulasi daftar laporan (bisa disesuaikan dengan kebutuhan)
            $laporan = [
                [
                    'id' => 1,
                    'jenis' => 'Laporan Stok',
                    'tanggal' => now()->format('Y-m-d'),
                    'deskripsi' => 'Laporan stok barang per tanggal ' . now()->format('d/m/Y')
                ],
                [
                    'id' => 2,
                    'jenis' => 'Laporan Transaksi',
                    'tanggal' => now()->format('Y-m-d'),
                    'deskripsi' => 'Laporan transaksi masuk dan keluar'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $laporan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET /laporan/{id} - Detail laporan
    public function show($id)
    {
        try {
            // Simulasi detail laporan
            $laporan = [
                'id' => $id,
                'jenis' => 'Laporan Stok',
                'tanggal' => now()->format('Y-m-d'),
                'data' => [
                    'total_barang' => Barang::count(),
                    'total_stok' => Barang::sum('stok'),
                    'barang_dibawah_minimum' => Barang::whereColumn('stok', '<', 'stok_minimum')->count()
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $laporan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST /laporan - Membuat laporan stok/transaksi
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang dan KepalaDivisi yang bisa membuat laporan
            if (!$user->hasRole(['AdminGudang', 'KepalaDivisi'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk membuat laporan'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'jenis_laporan' => 'required|in:stok,transaksi',
                'tanggal_awal' => 'nullable|date',
                'tanggal_akhir' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $jenisLaporan = $request->jenis_laporan;
            $tanggalAwal = $request->tanggal_awal ?? now()->startOfMonth()->format('Y-m-d');
            $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');

            if ($jenisLaporan === 'stok') {
                $data = [
                    'total_barang' => Barang::count(),
                    'total_stok' => Barang::sum('stok'),
                    'barang_dibawah_minimum' => Barang::whereColumn('stok', '<', 'stok_minimum')->count(),
                    'detail_barang' => Barang::with('kategori')->get()
                ];
            } else {
                $data = [
                    'transaksi_masuk' => Transaksi::where('jenis_transaksi', 'MASUK')
                        ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
                        ->with(['user', 'barang.kategori'])
                        ->get(),
                    'transaksi_keluar' => Transaksi::where('jenis_transaksi', 'KELUAR')
                        ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
                        ->with(['user', 'barang.kategori'])
                        ->get(),
                ];
            }

            $laporan = [
                'id' => rand(1000, 9999),
                'jenis' => $jenisLaporan === 'stok' ? 'Laporan Stok' : 'Laporan Transaksi',
                'tanggal' => now()->format('Y-m-d'),
                'periode' => [
                    'tanggal_awal' => $tanggalAwal,
                    'tanggal_akhir' => $tanggalAkhir
                ],
                'data' => $data
            ];

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dibuat',
                'data' => $laporan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET /laporan/{id}/pdf - Download laporan PDF
    public function downloadPdf($id)
    {
        try {
            // Simulasi - dalam implementasi nyata, gunakan library seperti DomPDF atau Snappy
            return response()->json([
                'success' => false,
                'message' => 'Fitur download PDF belum diimplementasikan. Silakan install library PDF seperti DomPDF.'
            ], 501);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh laporan PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // GET /laporan/{id}/excel - Download laporan Excel
    public function downloadExcel($id)
    {
        try {
            // Simulasi - dalam implementasi nyata, gunakan library seperti Maatwebsite/Excel
            return response()->json([
                'success' => false,
                'message' => 'Fitur download Excel belum diimplementasikan. Silakan install library Excel seperti Maatwebsite/Excel.'
            ], 501);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh laporan Excel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // POST /laporan/export - Mengekspor laporan dalam format tertentu
    public function export(Request $request)
    {
        try {
            $user = $request->user();
            
            // Hanya AdminGudang dan KepalaDivisi yang bisa export laporan
            if (!$user->hasRole(['AdminGudang', 'KepalaDivisi'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengekspor laporan'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'jenis_laporan' => 'required|in:stok,transaksi',
                'format' => 'required|in:pdf,excel',
                'tanggal_awal' => 'nullable|date',
                'tanggal_akhir' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Simulasi - dalam implementasi nyata, generate file dan return download
            return response()->json([
                'success' => false,
                'message' => 'Fitur export laporan belum diimplementasikan. Silakan install library PDF/Excel seperti DomPDF atau Maatwebsite/Excel.'
            ], 501);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengekspor laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Method khusus untuk KepalaDivisi: buatLaporan() - sesuai class diagram
    public function buatLaporan(Request $request)
    {
        // Memanggil method store yang sudah ada
        return $this->store($request);
    }

    // Method khusus untuk KepalaDivisi: cetakLaporan() - sesuai class diagram
    public function cetakLaporan(Request $request, $id = null)
    {
        try {
            $user = $request->user();
            
            // Hanya KepalaDivisi yang bisa mencetak laporan
            if (!$user->hasRole('KepalaDivisi')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Kepala Divisi yang dapat mencetak laporan'
                ], 403);
            }

            // Jika ada id, gunakan downloadPdf atau downloadExcel
            if ($id) {
                $format = $request->get('format', 'pdf');
                if ($format === 'excel') {
                    return $this->downloadExcel($id);
                }
                return $this->downloadPdf($id);
            }

            // Jika tidak ada id, gunakan export
            return $this->export($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencetak laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

