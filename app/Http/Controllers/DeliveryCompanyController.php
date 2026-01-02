<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Delivery;

class DeliveryCompanyController extends Controller
{
    public function listDeliveryRequests()
    {
        $deliveries = Delivery::where('delivery_company_id', auth()->id())
            ->orWhere('status', 'pending')
            ->with('purchase.car', 'purchase.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['message' => 'طلبات التوصيل', 'deliveries' => $deliveries], 200);
    }

    public function acceptDelivery($id)
    {
        $delivery = Delivery::where('id', $id)->where('status', 'pending')->first();
        if (!$delivery) {
            return response()->json(['message' => 'طلب التوصيل غير موجود'], 404);
        }
        $delivery->update(['delivery_company_id' => auth()->id(), 'status' => 'accepted']);
        return response()->json(['message' => 'تم قبول طلب التوصيل', 'delivery' => $delivery], 200);
    }

    public function updateDeliveryStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,in_transit,delivered,cancelled',
            'proof_of_delivery' => 'nullable|string',
        ]);

        $delivery = Delivery::where('id', $id)->where('delivery_company_id', auth()->id())->first();
        if (!$delivery) {
            return response()->json(['message' => 'التوصيل غير موجود'], 404);
        }

        $delivery->update($validated);
        return response()->json(['message' => 'تم تحديث حالة التوصيل', 'delivery' => $delivery], 200);
    }

    public function viewEarnings()
    {
        $totalEarnings = Delivery::where('delivery_company_id', auth()->id())
            ->where('status', 'delivered')
            ->sum('delivery_cost');

        return response()->json(['message' => 'إجمالي الأرباح', 'total_earnings' => $totalEarnings], 200);
    }
}
