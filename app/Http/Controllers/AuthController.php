<?php

namespace App\Http\Controllers;

use App\Models\Talent;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    use ApiResponse;

    #[OA\Post(path: "/auth/register", summary: "Register a new user", tags: ["Authentication"])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ["name","email","password","password_confirmation","phone","role"],
        properties: [
            new OA\Property(property: "name", type: "string", example: "Budi Santoso"),
            new OA\Property(property: "email", type: "string", format: "email", example: "budi@email.com"),
            new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
            new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "password123"),
            new OA\Property(property: "phone", type: "string", example: "081234567890"),
            new OA\Property(property: "role", type: "string", example: "talent"),
            new OA\Property(property: "stage_name", type: "string", example: "The Broken Strings", description: "Opsional, hanya digunakan jika role talent")
        ]
    ))]
    #[OA\Response(response: 201, description: "Registrasi berhasil")]
    #[OA\Response(response: 422, description: "Validasi gagal")]
    public function register(Request $request)
    {
        // Pesan error kustom agar lebih mudah dibaca oleh pengguna
        $messages = [
            'required' => 'Kolom :attribute wajib diisi.',
            'string' => 'Kolom :attribute harus berupa teks.',
            'email' => 'Format email tidak valid.',
            'unique' => ':attribute sudah terdaftar, silakan gunakan yang lain.',
            'min' => ':attribute minimal harus :min karakter.',
            'confirmed' => 'Konfirmasi password tidak cocok.',
            'max' => ':attribute maksimal :max karakter.',
            'in' => 'Pilihan :attribute tidak valid.',
            'prohibited_unless' => 'Kolom :attribute hanya boleh diisi untuk role talent.',
        ];

        // Validasi semua field yang dikirim
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'required|string|max:20',
            'role' => ['required', Rule::in(['talent', 'eo'])],
            'stage_name' => 'prohibited_unless:role,talent|nullable|string|max:255',
        ], $messages);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        $talent = null;

        // Gunakan transaksi agar data user dan talent tersimpan bersamaan atau tidak sama sekali
        DB::beginTransaction();
        try {
            // Buat akun user baru
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => $request->role,
            ]);

            // Jika role talent, buat profil talent sekaligus
            if ($user->role === 'talent') {
                $talent = Talent::create([
                    'id' => $user->id,
                    'user_id' => $user->id,
                    'stage_name' => $request->filled('stage_name') ? $request->stage_name : $user->name,
                    'city' => $request->input('city', ''),
                    'verified' => false,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Register failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }

        // Generate token autentikasi untuk user yang baru terdaftar
        $token = $user->createToken('auth_token')->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token
        ];

        if ($talent) {
            $data['talent'] = $talent;
        }

        return $this->successResponse($data, 'Registrasi berhasil', 201);
    }

    #[OA\Post(path: "/auth/login", summary: "Login user", tags: ["Authentication"])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ["email","password"],
        properties: [
            new OA\Property(property: "email", type: "string", format: "email", example: "budi@email.com"),
            new OA\Property(property: "password", type: "string", format: "password", example: "password123")
        ]
    ))]
    #[OA\Response(response: 200, description: "Login berhasil")]
    #[OA\Response(response: 401, description: "Email atau password salah")]
    public function login(Request $request)
    {
        // Pesan error kustom agar lebih mudah dibaca
        $messages = [
            'required' => 'Kolom :attribute wajib diisi.',
            'email' => 'Format email tidak valid.',
        ];

        // Validasi input email dan password
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ], $messages);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        // Cari user berdasarkan email, lalu verifikasi passwordnya
        $user = User::where('email', $request->email)->first();

        // Tolak login jika email tidak ditemukan atau password salah
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Generate token autentikasi untuk sesi login ini
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'Login berhasil');
    }

    #[OA\Post(path: "/auth/logout", summary: "Logout user", security: [["bearerAuth" => []]], tags: ["Authentication"])]
    #[OA\Response(response: 200, description: "Logout berhasil")]
    public function logout(Request $request)
    {
        // Hapus token yang sedang aktif untuk mengakhiri sesi
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse('Logout berhasil');
    }

    #[OA\Get(path: "/auth/me", summary: "Get current logged in user", security: [["bearerAuth" => []]], tags: ["Authentication"])]
    #[OA\Response(response: 200, description: "Success")]
    public function me(Request $request)
    {
        return $this->successResponse($request->user(), 'OK');
    }
}
