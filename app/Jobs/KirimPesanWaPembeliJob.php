<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\WhatsappTrait;
use DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Log;

class KirimPesanWaPembeliJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, WhatsappTrait;

    protected $invoiceId;
    protected $type;
    protected $statusPembayaran;

    public function __construct($invoiceId, $type = 'listBarang', $statusPembayaran = null)
    {
        $this->invoiceId = $invoiceId;
        $this->type = $type;
        $this->statusPembayaran = $statusPembayaran;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            DB::table('tbl_invoice')->where('id', $this->invoiceId)->update(['wa_status' => 'pending']);

            $invoice = DB::table('tbl_invoice as a')
                ->join('tbl_pembeli as b', 'a.pembeli_id', '=', 'b.id')
                ->join('tbl_status as d', 'a.status_id', '=', 'd.id')
                ->select('a.id', 'a.no_invoice', 'a.tanggal_invoice', 'b.marking','b.nama_pembeli', 'a.alamat', 'a.metode_pengiriman', 'a.total_harga', 'a.matauang_id', 'a.rate_matauang', 'd.status_name', 'b.no_wa')
                ->where('a.id', $this->invoiceId)
                ->first();

            if (!$invoice) {
                throw new \Exception("Invoice tidak ditemukan");
            }
            $no_invoice = $invoice->no_invoice;

            $resiData = DB::table('tbl_resi')
                ->where('invoice_id', $invoice->id)
                ->get(['no_resi', 'no_do', 'priceperkg', 'berat', 'panjang', 'lebar', 'tinggi', 'harga']);

            if ($resiData->isEmpty()) {
                throw new \Exception("Tidak ada resi yang terkait dengan invoice ini");
            }

            $pesan = ''; // Initialize pesan

            Log::info('Type for invoice ID ' . $this->invoiceId . ': ' . $this->type);

            if ($this->type !== 'invoice') {
                if ($invoice->metode_pengiriman === 'Pickup') {
                    $pesan = "*List barang* dengan no resi berikut telah siap untuk di pickup";
                } elseif ($invoice->metode_pengiriman === 'Delivery') {
                    $pesan = "*List barang* dengan no resi berikut telah siap untuk diantarkan.";
                } else {
                    throw new \Exception("Metode pengiriman tidak valid untuk invoice dengan ID $this->invoiceId");
                }
            } else {
                $pesan = "Silahkan download file di atas untuk melihat invoice";
            }

            try {
                Log::info('Memulai pembuatan PDF untuk invoice ID: ' . $invoice->id);

                $pdf = Pdf::loadView('exportPDF.notification', [
                    'invoice' => $invoice,
                    'resiData' => $resiData,
                    'hargaIDR' => $invoice->total_harga,
                    'type' => $this->type,
                    'tanggal' => $invoice->tanggal_invoice,
                    'statusPembayaran' => $this->statusPembayaran
                ]);

                Log::info('Invoice Data:', [
                    'invoice_id' => $invoice->id,
                    'nama_pembeli' => $invoice->nama_pembeli,
                    'total_harga' => $invoice->total_harga,
                    'tanggal_invoice' => $invoice->tanggal_invoice,
                    'statusPembayaran' => $this->statusPembayaran,
                    'resiData' => $resiData,
                    'type' => $this->type
                ]);

                Log::info('Berhasil membuat PDF untuk invoice ID: ' . $invoice->id);

            } catch (\Exception $e) {
                Log::error('Error generating PDF: ' . $e->getMessage(), ['exception' => $e]);
                return response()->json(['error' => 'Failed to generate PDF'], 500);
            }

            try {
                $pdfFileName = 'GES_'. $no_invoice .'.pdf';
                $filePath = storage_path('app/public/list_barang/' . $pdfFileName);
                $pdf->save($filePath);
            } catch (\Exception $e) {
                Log::error('Error saving PDF: ' . $e->getMessage(), ['exception' => $e]);
                return response()->json(['error' => 'Failed to save PDF'], 500);
            }

            $fileUrl = asset('storage/list_barang/' . $pdfFileName);
            $pesan .= "\n\n*Download list barang diatas";


            if ($invoice->no_wa) {
                $pesanTerkirimDenganFile = $this->kirimPesanWhatsapp($invoice->no_wa, $pesan, $fileUrl);
                $pesanTerkirim = $this->kirimPesanWhatsapp($invoice->no_wa, $pesan);


                if (!$pesanTerkirim || !$pesanTerkirimDenganFile) {

                    Log::error("Gagal mengirim pesan WhatsApp ke " . $invoice->no_wa);
                    DB::table('tbl_invoice')->where('id', $this->invoiceId)->update(['wa_status' => 'failed']);
                } else {
                    DB::table('tbl_invoice')->where('id', $this->invoiceId)->update(['wa_status' => 'sent']);
                }
            } else {
                Log::warning("Nomor WhatsApp tidak ditemukan untuk pembeli dengan ID: " . $invoice->id);
            }

            if (file_exists($filePath)) {
                unlink($filePath);
                Log::info('PDF berhasil dihapus: ' . $filePath);
            } else {
                Log::warning('PDF tidak ditemukan untuk dihapus: ' . $filePath);
            }
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            DB::table('tbl_invoice')->where('id', $this->invoiceId)->update(['wa_status' => 'failed']);
        }
    }
}
