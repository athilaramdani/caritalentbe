<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    use ApiResponse;

    #[OA\Put(path: "/users/profile", summary: "Update User Profile", security: [["bearerAuth" => []]], tags: ["User & Profile"])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "name", type: "string"),
            new OA\Property(property: "phone", type: "string")
        ]
    ))]
    #[OA\Response(response: 200, description: "Profil berhasil diperbarui")]
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $messages = [
            'required' => 'Kolom :attribute wajib diisi jika ingin diubah.',
            'string' => 'Kolom :attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
        ], $messages);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('phone')) $user->phone = $request->phone;
        
        $user->save();

        return $this->successResponse($user, 'Profil berhasil diperbarui');
    }

    #[OA\Put(path: "/users/password", summary: "Change User Password", security: [["bearerAuth" => []]], tags: ["User & Profile"])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ["current_password","new_password","new_password_confirmation"],
        properties: [
            new OA\Property(property: "current_password", type: "string", format: "password"),
            new OA\Property(property: "new_password", type: "string", format: "password"),
            new OA\Property(property: "new_password_confirmation", type: "string", format: "password")
        ]
    ))]
    #[OA\Response(response: 200, description: "Password berhasil diubah")]
    #[OA\Response(response: 422, description: "Password lama tidak sesuai")]
    public function updatePassword(Request $request)
    {
        $user = $request->user();
        
        $messages = [
            'required' => 'Kolom :attribute wajib diisi.',
            'min' => ':attribute minimal harus :min karakter.',
            'confirmed' => 'Konfirmasi password baru tidak cocok.',
            'string' => 'Kolom :attribute harus berupa teks.',
        ];

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], $messages);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Password lama tidak sesuai', 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->successResponse(null, 'Password berhasil diubah');
    }
}
