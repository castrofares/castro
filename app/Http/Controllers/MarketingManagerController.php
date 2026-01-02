<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Offer;
use App\Models\Campaign;

class MarketingManagerController extends Controller
{
    public function createOffer(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'nullable|exists:cars,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
        ]);

        $offer = Offer::create(array_merge($validated, ['created_by' => auth()->id()]));
        return response()->json(['message' => 'تم إنشاء العرض بنجاح', 'offer' => $offer], 201);
    }

    public function listOffers()
    {
        $offers = Offer::where('created_by', auth()->id())->with('car')->get();
        return response()->json(['message' => 'قائمة العروض', 'offers' => $offers], 200);
    }

    public function updateOffer(Request $request, $id)
    {
        $offer = Offer::where('created_by', auth()->id())->find($id);
        if (!$offer) {
            return response()->json(['message' => 'العرض غير موجود'], 404);
        }
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $offer->update(array_filter($validated));
        return response()->json(['message' => 'تم تحديث العرض بنجاح', 'offer' => $offer], 200);
    }

    public function deleteOffer($id)
    {
        $offer = Offer::where('created_by', auth()->id())->find($id);
        if (!$offer) {
            return response()->json(['message' => 'العرض غير موجود'], 404);
        }
        $offer->delete();
        return response()->json(['message' => 'تم حذف العرض بنجاح'], 200);
    }

    public function createCampaign(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:email,sms,banner,social_media',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
        ]);

        $campaign = Campaign::create(array_merge($validated, ['created_by' => auth()->id()]));
        return response()->json(['message' => 'تم إنشاء الحملة بنجاح', 'campaign' => $campaign], 201);
    }

    public function listCampaigns()
    {
        $campaigns = Campaign::where('created_by', auth()->id())->get();
        return response()->json(['message' => 'قائمة الحملات', 'campaigns' => $campaigns], 200);
    }
}
