<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PopupController extends Controller
{
    public function index()
    {
        $popupData = DB::table('tbl_popup')->first();
        return view('content.popup.indexpopup', compact('popupData'));
    }

    public function addPopup(Request $request)
    {
        $judulPopup = $request->input('judulPopup');
        $parafPopup = $request->input('parafPopup');
        $linkPopup = $request->input('linkPopup');
        $imagePopup = $request->file('imagePopup');

        try {
            $fileName = null;
            $existingData = DB::table('tbl_popup')->first();

            if ($imagePopup) {
                $fileName = 'Popup_' . $imagePopup->getClientOriginalName();
                $imagePopup->storeAs('public/images', $fileName);
            }
            

            if ($existingData) {
                DB::table('tbl_popup')->update([
                    'Judul_Popup' => $judulPopup,
                    'Paraf_Popup' => $parafPopup,
                    'Link_Popup' => $linkPopup,
                    'Image_Popup' => $fileName,
                    'updated_at' => now(),
                ]);
                $id = $existingData->id;
            } else {
                $id = DB::table('tbl_popup')->insertGetId([
                    'Judul_Popup' => $judulPopup,
                    'Paraf_Popup' => $parafPopup,
                    'Link_Popup' => $linkPopup,
                    'Image_Popup' => $fileName,
                    'created_at' => now(),
                ]);
            }
            $popupData = DB::table('tbl_popup')->where('id', $id)->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'data' => [
                    'id' => $popupData->id,
                    'imagePopup' => $popupData->Image_Popup,
                    'judulPopup' => $popupData->Judul_Popup,
                    'parafPopup' => $popupData->Paraf_Popup,
                    'linkPopup' => $popupData->Link_Popup
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }
    }



    public function destroyPopup(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['status' => 'warning', 'message' => 'ID tidak ada. Tidak dapat menghapus data.'], 400);
        }

        try {
            $deleted = DB::table('tbl_popup')->where('id', $id)->delete();

            if ($deleted) {
                return response()->json(['status' => 'success', 'message' => 'Data berhasil dihapus'], 200);
            } else {
                return response()->json(['status' => 'info', 'message' => 'Data tidak ditemukan'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus data: ' . $e->getMessage()], 500);
        }
    }

}