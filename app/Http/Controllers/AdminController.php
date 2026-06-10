<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Talent;
use App\Models\Event;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    use ApiResponse;

    #[OA\Get(path: "/admin/dashboard", summary: "Get Admin Dashboard Stats", security: [["bearerAuth" => []]], tags: ["Admin"])]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 403, description: "Forbidden")]
    public function dashboard()
    {
        // Pastikan hanya admin yang bisa mengakses dashboard ini
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        // Hitung statistik keseluruhan dari masing-masing tabel
        $totalUsers       = User::count();
        $totalTalents     = User::where('role', 'talent')->count();
        $totalEO          = User::where('role', 'eo')->count();
        $totalEvents      = Event::count();
        $activeEvents     = Event::where('status', 'dibuka')->count();
        $totalBookings    = Booking::count();
        $completedBookings = Booking::where('status', 'completed')->count();
        $totalReviews     = Review::count();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard stats',
            'data' => [
                'total_users'        => $totalUsers,
                'total_talents'      => $totalTalents,
                'total_eo'           => $totalEO,
                'total_events'       => $totalEvents,
                'active_events'      => $activeEvents,
                'total_bookings'     => $totalBookings,
                'completed_bookings' => $completedBookings,
                'total_reviews'      => $totalReviews,
            ]
        ], 200);
    }

    #[OA\Get(path: "/admin/users", summary: "Get All Users", security: [["bearerAuth" => []]], tags: ["Admin"])]
    #[OA\Parameter(name: "role", in: "query", required: false, schema: new OA\Schema(type: "string", enum: ["eo", "talent"]))]
    #[OA\Parameter(name: "search", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 403, description: "Forbidden")]
    public function getUsers(Request $request)
    {
        // Pastikan hanya admin yang bisa mengakses daftar user
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $query = User::query();

        // Filter berdasarkan role jika ada query param yang dikirim
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter pencarian berdasarkan nama atau email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Ambil hasilnya dengan paginasi 15 per halaman
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
        // Pastikan hanya admin yang bisa menghapus user
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        // Cari user yang akan dihapus
        $user = User::find($id);

        // Pastikan user tersebut ada
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        // Cegah admin menghapus sesama admin
        if ($user->role === 'admin') {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus sesama admin'], 400);
        }

        // Hapus akun user dari database
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
        // Pastikan hanya admin yang bisa memverifikasi talent
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        // Validasi input, field verified wajib berupa boolean
        $validator = Validator::make($request->all(), [
            'verified' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        // Cari profil talent berdasarkan user ID
        $talent = Talent::where('user_id', $id)->first();

        // Pastikan profil talent tersebut ada
        if (!$talent) {
            return response()->json(['success' => false, 'message' => 'Talent profile tidak ditemukan'], 404);
        }

        // Update status verifikasi talent
        $isVerified = filter_var($request->verified, FILTER_VALIDATE_BOOLEAN);
        $talent->update(['verified' => $isVerified]);

        // Kirim notifikasi ke talent mengenai perubahan status verifikasi
        Notification::create([
            'user_id' => $talent->user_id,
            'title' => $isVerified ? 'Talent Berhasil Diverifikasi' : 'Verifikasi Talent Dicabut',
            'body' => $isVerified
                ? 'Profil talent kamu sudah diverifikasi oleh admin.'
                : 'Verifikasi profil talent kamu dicabut oleh admin.',
            'type' => 'talent',
            'action' => $isVerified ? 'talent_verified' : 'talent_unverified',
            'reference_type' => 'talent',
            'reference_id' => $talent->user_id,
            'data' => [
                'talent_id' => $talent->user_id,
                'stage_name' => $talent->stage_name,
                'verified' => $isVerified,
            ],
        ]);

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
                new OA\Property(property: "status", type: "string", enum: ["dibuka", "ditutup", "selesai", "dibatalkan"], example: "dibatalkan"),
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
        // Pastikan hanya admin yang bisa melakukan moderasi event
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        // Validasi status dan alasan moderasi
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:dibuka,ditutup,selesai,dibatalkan',
            'reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        // Cari event yang akan dimoderasi
        $event = Event::find($id);

        // Pastikan event tersebut ada
        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event tidak ditemukan'], 404);
        }

        // Update status event sesuai keputusan admin
        $event->update([
            'status' => $request->status
        ]);

        // Beritahu EO pemilik event bahwa eventnya telah dimoderasi
        Notification::create([
            'user_id' => $event->organizer_id,
            'title' => 'Event Dimoderasi Admin',
            'body' => 'Event "' . $event->title . '" telah diubah statusnya menjadi '. $request->status .' oleh Admin.' . ($request->filled('reason') ? ' Alasan: ' . $request->reason : ''),
            'type' => 'event',
            'action' => 'event_moderated',
            'reference_type' => 'event',
            'reference_id' => $event->id,
            'data' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'status' => $request->status,
                'reason' => $request->reason,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil dimoderasi'
        ], 200);
    }
}
