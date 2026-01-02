<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Cart;
use App\Models\CarImage;

use Illuminate\Support\Facades\Storage;


class LocalAdminController extends Controller
{
    public function createCar(Request $request)
    {
        // ✅ التحقق من صحة البيانات المدخلة
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'brand'       => 'required|string|max:255',
            'model'       => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'images'      => 'nullable|array', // الصور كمصفوفة
            'images.*'    => 'image|mimes:jpeg,png,jpg,gif|max:2048', // التحقق من الملفات
        ]);

        // ✅ تسجيل البيانات القادمة من Postman في ملف `laravel.log`
        logger("📌 Received Request Data: " . json_encode($request->all()));

        // ✅ إنشاء السيارة
        $car = Car::create([
            'name'        => $validated['name'],
            'brand'       => $validated['brand'],
            'model'       => $validated['model'],
            'price'       => $validated['price'],
            'description' => $validated['description'] ?? null,
            'user_id'     => auth()->id(),
            'status'      => 'pending',
        ]);

        // ✅ إضافة السيارة إلى جدول `cart` (Many-to-Many)
        Cart::create([
            'user_id' => auth()->id(),
            'car_id'  => $car->id,
        ]);
        logger("✅ Car registered in cart: User ID " . auth()->id() . " - Car ID " . $car->id);

        // ✅ التأكد من وجود صور في الطلب
        // if ($request->hasFile('images')) {
        //     logger("✅ Images Found: " . count($request->file('images')) . " images.");

        //     foreach ($request->file('images') as $image) {
        //         // 🔍 تسجيل معلومات الصورة قبل حفظها
        //         logger("Uploading Image: " . $image->getClientOriginalName());

        //         // ✅ تخزين الصورة في `storage/app/public/cars/`
        //         $path = $image->store('cars', 'public');

        //         // ✅ حفظ مسار الصورة في جدول `car_images`
        //         CarImage::create([
        //             'car_id'     => $car->id,
        //             'image_path' => $path,
        //         ]);

        //         // 🔍 تأكيد حفظ الصورة
        //         logger("✅ Image Stored at: " . $path);
        //     }
        // }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('cars', $fileName, 'public');
                CarImage::create([
                    'car_id' => $car->id,
                    'image_path' => $path
                ]);
            }
        }
         else {
            logger("❌ No Images Found in the Request!");
        }

        // ✅ استرجاع السيارة مع الصور المرتبطة بها وإرسالها في الاستجابة
        return response()->json([
            'message' => '🚀 Car created successfully!',
            'car'     => $car->load('images'), // تحميل الصور مع السيارة
        ], 201);
    }
    /**
     * حذف سيارة.
     */
    public function deleteCar($id)
    {
        $car = Car::where('user_id', auth()->id())->find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        // حذف الصور المرتبطة بالسيارة
        foreach ($car->images as $image) {
            Storage::disk('public')->delete($image->image_path); // حذف الصورة من التخزين
            $image->delete(); // حذف الصورة من قاعدة البيانات
        }

        $car->delete();

        return response()->json([
            'message' => 'Car deleted successfully.',
        ], 200);
    }

    /**
     * تعديل بيانات سيارة.
     */
    public function updateCar(Request $request, $id)
    {
        $car = Car::where('user_id', auth()->id())->find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'string|max:255|nullable',
            'brand' => 'string|max:255|nullable',
            'model' => 'string|max:255|nullable',
            'price' => 'numeric|min:0|nullable',
            'description' => 'nullable|string',
        ]);

        $car->update(array_filter($validated));

        return response()->json([
            'message' => 'Car updated successfully.',
            'car' => $car,
        ], 200);
    }

    /**
     * عرض سيارة.
     */
    public function showCar($id)
    {
        $car = Car::with('images')->find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        return response()->json($car, 200);
    }
    public function listCars()
    {
        // ✅ التحقق من أن المستخدم الحالي هو Local Admin
        if (auth()->user()->role !== 'local_admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // ✅ جلب السيارات التي قام هذا المستخدم بإضافتها فقط
        $cars = Car::where('user_id', auth()->id())->with('images')->get();

        return response()->json([
            'message' => '🚗 قائمة السيارات الخاصة بك:',
            'cars' => $cars
        ], 200);
    }
}
