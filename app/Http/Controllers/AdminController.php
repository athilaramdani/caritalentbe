<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Talent;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    #[OA\Get(path: "/admin/users", summary: "Get All Users", security: [["bearerAuth" => []]], tags: ["Admin"])]
    #[OA\Parameter(name: "role", in: "query", required: false, schema: new OA\Schema(type: "string", enum: ["eo", "talent"]))]
    #[OA\Parameter(name: "search", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 403, description: "Forbidden")]
    public function getUsers(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage()
                ]
            ]
        ], 200);
    }

    #[OA\Delete(path: "/admin/users/{id}", summary: "Delete User", security: [["bearerAuth" => []]], tags: ["Admin"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 403, description: "Forbidden")]
    #[OA\Response(response: 404, description: "Not Found")]
    public function deleteUser($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        if ($user->role === 'admin') {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus sesama admin'], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun pengguna berhasil dihapus'
        ], 200);
    }

    #[OA\Put(path: "/admin/talents/{id}/verify", summary: "Verify Talent", security: [["bearerAuth" => []]], tags: ["Admin"])]
    #[OA\Parameter(name: "id", in: "path", required: true, description: "Talent User ID", schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "verified", type: "boolean")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 403, description: "Forbidden")]
    #[OA\Response(response: 404, description: "Not Found")]
    public function verifyTalent(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validator = Validator::make($request->all(), [
            'verified' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $talent = Talent::where('user_id', $id)->first();

        if (!$talent) {
            return response()->json(['success' => false, 'message' => 'Talent profile tidak ditemukan'], 404);
        }

        $isVerified = filter_var($request->verified, FILTER_VALIDATE_BOOLEAN);
        $talent->update(['verified' => $isVerified]);

        return response()->json([
            'success' => true,
            'message' => $isVerified ? 'Talent berhasil diverifikasi' : 'Verifikasi talent dicabut',
            'data' => [
                'id' => $talent->user_id,
                'stage_name' => $talent->stage_name,
                'verified' => $talent->verified
            ]
        ], 200);
    }

    #[OA\Put(path: "/admin/events/{id}/moderate", summary: "Moderate Event (Admin)", security: [["bearerAuth" => []]], tags: ["Admin"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "cancelled"),
                new OA\Property(property: "reason", type: "string")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 403, description: "Forbidden")]
    #[OA\Response(response: 404, description: "Not Found")]
    public function moderateEvent(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $event = Event::find($id);

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event tidak ditemukan'], 404);
        }

        $event->update([
            'status' => $request->status
        ]);

        if ($request->has('reason')) {
            // Give notification to EO
            \App\Models\Notification::create([
                'user_id' => $event->organizer_id,
                'title' => 'Event Dimoderasi Admin',
                'body' => 'Event "' . $event->title . '" telah diubah statusnya menjadi '. $request->status .' oleh Admin. Alasan: ' . $request->reason,
                'type' => 'application',
                'reference_id' => $event->id
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil dimoderasi'
        ], 200);
    }
}
