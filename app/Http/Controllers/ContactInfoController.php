<?php

namespace App\Http\Controllers;

use App\Models\ContactInfo;
use Illuminate\Http\Request;

class ContactInfoController extends Controller
{
    /**
     * Get contact info for the authenticated local admin
     */
    public function show()
    {
        try {
            $contactInfo = ContactInfo::where('user_id', auth()->id())->first();

            if (!$contactInfo) {
                return response()->json([
                    'message' => 'لا توجد معلومات اتصال',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'message' => 'تم جلب معلومات الاتصال بنجاح',
                'data' => $contactInfo
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب معلومات الاتصال',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create or update contact info for the authenticated local admin
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'business_name' => 'required|string|max:255',
                'primary_phone' => 'required|string|max:20',
                'secondary_phone' => 'nullable|string|max:20',
                'email' => 'required|email|max:255',
                'whatsapp' => 'nullable|string|max:20',
                'address' => 'required|string',
                'city' => 'required|string|max:100',
                'business_hours' => 'nullable|array',
                'social_media' => 'nullable|array',
            ]);

            $contactInfo = ContactInfo::updateOrCreate(
                ['user_id' => auth()->id()],
                array_merge($validated, ['user_id' => auth()->id()])
            );

            return response()->json([
                'message' => 'تم حفظ معلومات الاتصال بنجاح',
                'data' => $contactInfo
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء حفظ معلومات الاتصال',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete contact info for the authenticated local admin
     */
    public function destroy()
    {
        try {
            $contactInfo = ContactInfo::where('user_id', auth()->id())->first();

            if (!$contactInfo) {
                return response()->json([
                    'message' => 'لا توجد معلومات اتصال للحذف'
                ], 404);
            }

            $contactInfo->delete();

            return response()->json([
                'message' => 'تم حذف معلومات الاتصال بنجاح'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف معلومات الاتصال',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
