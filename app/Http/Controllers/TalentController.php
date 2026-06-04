<?php

namespace App\Http\Controllers;

use App\Models\Talent;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class TalentController extends Controller
{
    use ApiResponse;

    private function formatTalent(Talent $talent): array
    {
        return [
            'id' => $talent->id,
            'user_id' => $talent->user_id,
            'stage_name' => $talent->stage_name,
            'genre' => $talent->genres->pluck('name')->values()->all(),
            'price_min' => $talent->price_min,
            'price_max' => $talent->price_max,
            'city' => $talent->city,
            'bio' => $talent->bio,
            'portfolio_link' => $talent->portfolio_link,
            'verified' => (bool) $talent->verified,
            'average_rating' => (float) $talent->average_rating,
            'total_reviews' => (int) $talent->total_reviews,
            'created_at' => $talent->created_at,
            'updated_at' => $talent->updated_at,
            'user' => $talent->user ? [
                'name' => $talent->user->name,
                'email' => $talent->user->email,
                'phone' => $talent->user->phone,
            ] : null,
        ];
    }

    #[OA\Get(path: "/talents", summary: "Get list of talents", tags: ["Talent Profile"])]
    #[OA\Parameter(name: "city", in: "query", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "genre", in: "query", schema: new OA\Schema(type: "string"))]
    #[OA\Response(response: 200, description: "Returns list of talents")]
    public function index(Request $request)
    {
        $query = Talent::with(['genres', 'user']);

        if ($request->has('city')) {
            $query->where('city', $request->city);
        }
        if ($request->has('verified')) {
            $query->where('verified', $request->verified === 'true' || $request->verified === '1');
        }
        if ($request->has('search')) {
            $query->where('stage_name', 'like', '%' . $request->search . '%');
        }
        if ($request->has('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->genre . '%');
            });
        }
        
        // Basic filtering for price_min and max
        if ($request->has('price_min')) {
            $query->where('price_min', '>=', $request->price_min);
        }
        if ($request->has('price_max')) {
            $query->where('price_max', '<=', $request->price_max);
        }

        $perPage = $request->input('per_page', 15);
        $talents = $query->paginate($perPage);
        $mappedTalents = collect($talents->items())->map(fn ($talent) => $this->formatTalent($talent))->values();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'talents' => $mappedTalents,
                'pagination' => [
                    'current_page' => $talents->currentPage(),
                    'per_page' => $talents->perPage(),
                    'total' => $talents->total(),
                    'last_page' => $talents->lastPage()
                ]
            ]
        ]);
    }

    #[OA\Get(path: "/talents/{id}", summary: "Get talent by id", tags: ["Talent Profile"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Returns talent detail")]
    #[OA\Response(response: 404, description: "Talent tidak ditemukan")]
    public function show($id)
    {
        $talent = Talent::with(['genres', 'user'])->find($id);

        if (!$talent) {
            return $this->errorResponse('Talent tidak ditemukan', 404);
        }

        return $this->successResponse($this->formatTalent($talent), 'OK');
    }

    #[OA\Get(path: "/talents/my", summary: "Get my talent profile", security: [["bearerAuth" => []]], tags: ["Talent Profile"])]
    #[OA\Response(response: 200, description: "Returns current user's talent profile")]
    #[OA\Response(response: 404, description: "Profil talent tidak ditemukan")]
    public function myTalent(Request $request)
    {
        $talent = Talent::with(['genres', 'user'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$talent) {
            return $this->errorResponse('Profil talent tidak ditemukan', 404);
        }

        return $this->successResponse($this->formatTalent($talent), 'OK');
    }

    #[OA\Post(path: "/talents", summary: "Create talent profile", security: [["bearerAuth" => []]], tags: ["Talent Profile"])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(
        required: ["stage_name", "city"],
        properties: [
            new OA\Property(property: "stage_name", type: "string"),
            new OA\Property(property: "genre_ids", type: "array", items: new OA\Items(type: "integer")),
            new OA\Property(property: "price_min", type: "number"),
            new OA\Property(property: "price_max", type: "number"),
            new OA\Property(property: "city", type: "string"),
            new OA\Property(property: "bio", type: "string"),
            new OA\Property(property: "portfolio_link", type: "string")
        ]
    ))]
    #[OA\Response(response: 201, description: "Profil talent berhasil dibuat")]
    public function store(Request $request)
    {
        if ($request->user()->role !== 'talent') {
            return $this->errorResponse('Hanya talent yang bisa membuat profil', 403);
        }

        $validator = Validator::make($request->all(), [
            'stage_name' => 'required|string|max:255',
            'genre_ids' => 'sometimes|array',
            'genre_ids.*' => 'exists:genres,id',
            'price_min' => 'numeric|nullable',
            'price_max' => 'numeric|nullable',
            'city' => 'required|string|max:255',
            'bio' => 'string|nullable',
            'portfolio_link' => 'string|url|nullable',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        // Talent should only have one profile per user
        $existingTalent = Talent::where('user_id', $request->user()->id)->first();
        if ($existingTalent) {
            return $this->errorResponse('Profil talent sudah ada', 422);
        }

        $talent = Talent::create([
            'id' => $request->user()->id,
            'user_id' => $request->user()->id,
            'stage_name' => $request->stage_name,
            'price_min' => $request->price_min,
            'price_max' => $request->price_max,
            'city' => $request->city,
            'bio' => $request->bio,
            'portfolio_link' => $request->portfolio_link,
            'verified' => false,
        ]);

        if ($request->has('genre_ids')) {
            $talent->genres()->attach($request->genre_ids);
        }

        $talent->load(['genres', 'user']);

        return $this->successResponse($this->formatTalent($talent), 'Profil talent berhasil dibuat', 201);
    }

    #[OA\Put(path: "/talents/{id}", summary: "Update talent profile", security: [["bearerAuth" => []]], tags: ["Talent Profile"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "stage_name", type: "string"),
            new OA\Property(property: "price_min", type: "number"),
            new OA\Property(property: "price_max", type: "number"),
            new OA\Property(property: "bio", type: "string"),
            new OA\Property(property: "genre_ids", type: "array", items: new OA\Items(type: "integer"))
        ]
    ))]
    #[OA\Response(response: 200, description: "Profil talent berhasil diperbarui")]
    public function update(Request $request, $id)
    {
        $talent = Talent::find($id);

        if (!$talent) {
            return $this->errorResponse('Talent tidak ditemukan', 404);
        }

        // Only owner or admin can update
        if ($request->user()->id !== $talent->user_id && $request->user()->role !== 'admin') {
            return $this->errorResponse('Akses ditolak', 403);
        }

        $validator = Validator::make($request->all(), [
            'stage_name' => 'sometimes|string|max:255',
            'genre_ids' => 'sometimes|array',
            'genre_ids.*' => 'exists:genres,id',
            'price_min' => 'numeric|nullable',
            'price_max' => 'numeric|nullable',
            'city' => 'sometimes|string|max:255',
            'bio' => 'string|nullable',
            'portfolio_link' => 'string|url|nullable',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        $talent->update($request->only([
            'stage_name', 'price_min', 'price_max', 'city', 'bio', 'portfolio_link'
        ]));

        if ($request->has('genre_ids')) {
            $talent->genres()->sync($request->genre_ids);
        }

        $talent->load(['genres', 'user']);

        return $this->successResponse($this->formatTalent($talent), 'Profil talent berhasil diperbarui');
    }

    #[OA\Delete(path: "/talents/{id}", summary: "Delete talent profile", security: [["bearerAuth" => []]], tags: ["Talent Profile"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Profil talent berhasil dihapus")]
    public function destroy(Request $request, $id)
    {
        $talent = Talent::find($id);

        if (!$talent) {
            return $this->errorResponse('Talent tidak ditemukan', 404);
        }

        if ($request->user()->role !== 'admin') {
            return $this->errorResponse('Akses ditolak. Hanya admin yang dapat menghapus data ini.', 403);
        }

        $talent->delete();

        return $this->successResponse('Profil talent berhasil dihapus');
    }
}
