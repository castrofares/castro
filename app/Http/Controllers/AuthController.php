<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * تسجيل مستخدم جديد.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:super_admin,local_admin,marketing_manager,consultant,delivery_company,seller,buyer,renter,viewer,system_user',
        ]);

        // تحديد حالة المستخدم بناءً على الدور
        $status = ($validated['role'] === 'seller') ? 'pending' : 'active';

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
            'status'   => $status,
        ]);

        // إنشاء رمز مميز (توكن) للمستخدم
        $token = $user->createToken('auth_token')->plainTextToken;

        // رسالة مخصصة للبائعين
        $message = ($validated['role'] === 'seller')
            ? 'تم التسجيل بنجاح! حسابك قيد المراجعة وسيتم تفعيله بعد موافقة الإدارة.'
            : 'User registered successfully.';

        return response()->json([
            'message'      => $message,
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'role'   => $user->role,
                'status' => $user->status,
            ],
        ], 201);
    }
    /**
     * تسجيل الدخول والحصول على رمز مميز.
     */
    public function login(Request $request)
    {
        // التحقق من الحقول
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // البحث عن المستخدم حسب الإيميل
        $user = User::where('email', $credentials['email'])->first();

        // التحقق من كلمة المرور
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // التحقق من حالة المستخدم
        if ($user->status === 'pending') {
            return response()->json([
                'message' => 'حسابك قيد المراجعة. يرجى الانتظار حتى تتم الموافقة من قبل الإدارة.'
            ], 403);
        }

        if ($user->status === 'suspended') {
            return response()->json([
                'message' => 'تم تعليق حسابك. يرجى التواصل مع الإدارة.'
            ], 403);
        }

        // في هذه المرحلة بيانات الاعتماد صحيحة والحساب نشط
        // إنشاء رمز مميز (توكن) باستخدام Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Logged in successfully.',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
                'status' => $user->status,
                'phone' => $user->phone,
                'address' => $user->address,
                'region' => $user->region,
                'is_active' => $user->is_active,
            ],
        ], 200);
    }
    public function registerBuyer(Request $request)
{
    // التحقق من صحة البيانات المدخلة
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed', // تأكيد كلمة المرور
    ]);

    // إنشاء المستخدم مع تعيين الدور تلقائيًا كـ "buyer"
    $user = User::create([
        'name'     => $validated['name'],
        'email'    => $validated['email'],
        'password' => bcrypt($validated['password']),
        'role'     => 'buyer', // تعيين الدور تلقائيًا
    ]);

    // إنشاء رمز الوصول (Access Token) للمستخدم
    $token = $user->createToken('auth_token')->plainTextToken;

    // الاستجابة
    return response()->json([
        'message'      => 'Buyer registered successfully.',
        'access_token' => $token,
        'token_type'   => 'Bearer',
        'user'         => $user,
    ], 201);
}

    /**
     * تسجيل الخروج وإبطال الرموز.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.'], 200);
    }

    /**
     * الحصول على بيانات المستخدم الحالي.
     */
    public function me(Request $request)
    {
        return response()->json($request->user(), 200);
    }
}
