<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultation;

class ConsultantController extends Controller
{
    public function listRequests()
    {
        $consultations = Consultation::where('consultant_id', auth()->id())
            ->orWhere('status', 'pending')
            ->with('buyer', 'car')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'طلبات الاستشارات',
            'consultations' => $consultations,
        ], 200);
    }

    public function acceptRequest($id)
    {
        $consultation = Consultation::where('id', $id)->where('status', 'pending')->first();
        if (!$consultation) {
            return response()->json(['message' => 'طلب الاستشارة غير موجود'], 404);
        }
        $consultation->update(['consultant_id' => auth()->id(), 'status' => 'in_progress']);
        return response()->json(['message' => 'تم قبول طلب الاستشارة', 'consultation' => $consultation], 200);
    }

    public function respondToConsultation(Request $request, $id)
    {
        $validated = $request->validate(['response' => 'required|string', 'commission' => 'nullable|numeric|min:0']);
        $consultation = Consultation::where('id', $id)->where('consultant_id', auth()->id())->first();
        if (!$consultation) {
            return response()->json(['message' => 'الاستشارة غير موجودة'], 404);
        }
        $consultation->update(['response' => $validated['response'], 'commission' => $validated['commission'] ?? null, 'status' => 'completed']);
        return response()->json(['message' => 'تم إرسال الرد بنجاح', 'consultation' => $consultation], 200);
    }

    public function viewEarnings()
    {
        $totalEarnings = Consultation::where('consultant_id', auth()->id())->where('status', 'completed')->sum('commission');
        return response()->json(['message' => 'إجمالي الأرباح', 'total_earnings' => $totalEarnings], 200);
    }
}
