<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Jobs\AddTrackingJob;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Tracking;
use Yajra\DataTables\Facades\DataTables;

class TrackingsController extends Controller
{
    public function index()
    {
        $listStatus = DB::table('tbl_tracking')
            ->select('status')
            ->distinct()
            ->get();

        return view('Tracking.indextracking', [
            'listStatus' =>  $listStatus,
            'hasActionColumn' => in_array(Auth::user()->role, ['superadmin', 'admin', 'supervisor'])
        ]);
    }

    public function getTrackingData(Request $request)
    {
        $companyId = session('active_company_id');
        $user = auth()->user();

        $query = DB::table('tbl_tracking')
            ->select([
                'tbl_tracking.id',
                'tbl_tracking.no_resi',
                'tbl_tracking.no_do',
                'tbl_tracking.status',
                'tbl_tracking.keterangan',
                'tbl_invoice.status_bayar'
            ])
            ->leftJoin('tbl_resi', 'tbl_tracking.no_resi', '=', 'tbl_resi.no_resi')
            ->leftJoin('tbl_invoice', 'tbl_resi.invoice_id', '=', 'tbl_invoice.id')
            ->where('tbl_tracking.company_id', $companyId);

        // 🔹 Jika User adalah Customer
        if ($user->role === 'customer') {
            $query->addSelect([
                DB::raw("IFNULL(tbl_resi.berat, ROUND((tbl_resi.panjang * tbl_resi.lebar * tbl_resi.tinggi) / 1000000, 2)) AS berat"),
                DB::raw("IFNULL(ROUND((tbl_resi.panjang * tbl_resi.lebar * tbl_resi.tinggi) / 1000000, 2), '-') AS volume"),
                DB::raw("IF(tbl_resi.berat IS NOT NULL, CONCAT(tbl_resi.berat, ' Kg'), CONCAT(ROUND((tbl_resi.panjang * tbl_resi.lebar * tbl_resi.tinggi) / 1000000, 2), ' m³')) AS quantitas"),
                DB::raw("IFNULL(DATE_FORMAT(tbl_pengantaran_detail.tanggal_penerimaan, '%d %M %Y %H:%i:%s'), '-') AS tanggal_penerimaan")
            ])
            ->leftJoin('tbl_pembeli', 'tbl_invoice.pembeli_id', '=', 'tbl_pembeli.id')
            ->leftJoin('tbl_pengantaran_detail', 'tbl_invoice.id', '=', 'tbl_pengantaran_detail.invoice_id')
            ->where('tbl_pembeli.user_id', $user->id);
        }

        // 🔹 Filter berdasarkan status jika ada
        if ($request->status) {
            $query->where('tbl_tracking.status', $request->status);
        }

        // 🔹 Filter berdasarkan pencarian dari txSearch
        if ($request->search) {
            $searchValue = $request->search;
            $query->where(function ($q) use ($searchValue) {
                $q->where('tbl_tracking.no_resi', 'like', "%{$searchValue}%")
                  ->orWhere('tbl_tracking.no_do', 'like', "%{$searchValue}%")
                  ->orWhere('tbl_tracking.keterangan', 'like', "%{$searchValue}%");
            });
        }

        // $query->orderBy('tbl_tracking.id', 'desc');

        if (!$request->has('order')) {
            // Jika tidak ada sorting dari DataTables, gunakan default sorting ID DESC
            $query->orderBy('tbl_tracking.id', 'desc');
        } else {
            // Jika ada sorting dari DataTables, gunakan sorting yang diberikan
            $order = $request->order[0] ?? null;

            if ($order) {
                $columns = [
                    'no_resi' => 'tbl_tracking.no_resi',
                    'no_do' => 'tbl_tracking.no_do',
                    'status' => 'tbl_tracking.status',
                    'keterangan' => 'tbl_tracking.keterangan',
                    'status_bayar' => 'tbl_invoice.status_bayar'
                ];

                $columnIndex = $order['column'];
                $columnName = $request->columns[$columnIndex]['data'] ?? null;
                $direction = $order['dir'] ?? 'asc';

                // Pastikan hanya kolom yang valid yang bisa digunakan untuk sorting
                if ($columnName && isset($columns[$columnName])) {
                    $query->orderBy($columns[$columnName], $direction);
                }
            }
        }

        // 🔹 Ambil hanya ID yang sudah difilter
        $filteredIds = $query->pluck('tbl_tracking.id')->toArray();

        return DataTables::of($query)
            ->with(['filteredIds' => $filteredIds]) // Kirim hanya ID yang difilter
            ->addColumn('select', function ($row) {
                return $row->status === "Dalam Perjalanan"
                    ? '<input type="checkbox" class="select-row" data-id="' . $row->id . '">'
                    : '';
            })
            ->editColumn('status', function ($row) {
                $statusBadgeClass = match ($row->status) {
                    'Dalam Perjalanan' => 'badge-success',
                    'Batam / Sortir' => 'badge-primary',
                    'Delivering' => 'badge-success',
                    'Ready For Pickup' => 'badge-warning',
                    default => 'badge-secondary',
                };
                return '<span class="badge ' . $statusBadgeClass . '">' . $row->status . '</span>';
            })
            ->addColumn('status_bayar', function ($row) {
                if ($row->status_bayar == 'Lunas') {
                    return '<span class="text-success"><i class="fas fa-check-circle"></i> Lunas</span>';
                } elseif ($row->status_bayar == '-') {
                    return '<span class="text-muted">-</span>';
                } elseif (is_null($row->status_bayar)) {
                    return '-';
                } else {
                    return '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($row->status_bayar) . '</span>';
                }
            })
            ->addColumn('action', function ($row) {
                $deleteButton = $row->status == 'Dalam Perjalanan'
                    ? '<a href="#" class="btn btnDestroyTracking btn-sm btn-danger ml-2" data-id="' . $row->id . '"><i class="fas fa-trash"></i></a>'
                    : '';
                return '<a href="#" class="btn btnUpdateTracking btn-sm btn-secondary" data-id="' . $row->id . '"><i class="fas fa-edit"></i></a>' . $deleteButton;
            })
            ->rawColumns(['select', 'status', 'status_bayar', 'action'])
            ->make(true);
    }


    public function addTracking(Request $request)
    {
        $companyId = session('active_company_id');

        $request->validate([
            'noResi' => 'required|array|min:1',
            'noDeliveryOrder' => 'required|string|max:20',
            'status' => 'required|string|max:50',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            $jobId = Str::uuid()->toString();
            $noResiList = $request->input('noResi');

            $duplicateResi = array_diff_assoc($noResiList, array_unique($noResiList));
            $duplicateResi = array_unique($duplicateResi);

            if (!empty($duplicateResi)) {
                return response()->json([
                    'warning' => 'Duplicate noResi found. Only unique noResi will be processed.',
                    'duplicateResi' => array_values($duplicateResi),
                ], 400);
            }

            $chunkSize = 200;
            $chunks = array_chunk($noResiList, $chunkSize);
            $totalChunks = count($chunks);

            foreach ($chunks as $index => $chunk) {
                AddTrackingJob::dispatch([
                    'noResi' => $chunk,
                    'noDeliveryOrder' => $request->input('noDeliveryOrder'),
                    'status' => $request->input('status'),
                    'keterangan' => $request->input('keterangan'),
                ], $companyId, $jobId, $index, $totalChunks);
            }

            return response()->json(['success' => 'Data is being processed', 'jobId' => $jobId]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add data', 'message' => $e->getMessage()], 500);
        }
    }


    public function updateTracking(Request $request, $id)
    {
        $validated = $request->validate([
            'noDeliveryOrder' => 'required|string|max:20',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            $Tracking = Tracking::findOrFail($id);

            if (!$Tracking) {
                return response()->json(['message' => 'ID Tracking tidak ditemukan'], 400);
            }
            $Tracking->no_do = $request->input('noDeliveryOrder');
            $Tracking->keterangan = $request->input('keterangan');

            $Tracking->update($validated);
            return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['error' => false, 'message' => 'Data gagal diperbarui']);
        }

    }


    public function deleteTracking($id)
    {
        $Tracking = Tracking::findOrFail($id);

        if ($Tracking->status != "Dalam Perjalanan") {
            return response()->json([
                'status' => 'error',
                'message' => 'Tracking hanya bisa dihapus jika Status Dalam perjalanan silahkan merefresh halaman untuk mengupdate data'
            ], 400);
        }

        try {

            $Tracking->delete();

            return response()->json(['status' => 'success', 'message' => 'Data berhasil dihapus'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function show($id)
    {
        $Tracking = Tracking::findOrFail($id);
        return response()->json($Tracking);
    }

    public function deleteTrackingMultipe(Request $request)
    {
        $ids = (array) $request->input('ids');
        $ids = array_map('intval', $ids);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No IDs provided'], 400);
        }

        $trackings = Tracking::whereIn('id', $ids)->get();

        $idsToDelete = $trackings->filter(fn ($tracking) => $tracking->status === 'Dalam Perjalanan')
                                 ->pluck('id')
                                 ->toArray();

        if (empty($idsToDelete)) {
            return response()->json([
                'success' => false,
                'message' => 'No records with status "Dalam Perjalanan" to delete. Please refresh the page to update data.'
            ], 400);
        }

        $deletedCount = Tracking::whereIn('id', $idsToDelete)->delete();

        return response()->json([
            'success' => true,
            'message' => "$deletedCount record(s) deleted successfully."
        ]);
    }

}
