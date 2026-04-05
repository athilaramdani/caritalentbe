<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Talent;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class TalentController extends Controller
{
    use ApiResponse;

    #[OA\Get(path: "/talents", summary: "Get list of talents", tags: ["Talent Profile"])]
    #[OA\Parameter(name: "city", in: "query", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "genre", in: "query", schema: new OA\Schema(type: "string"))]
    #[OA\Response(response: 200, description: "Returns list of talents")]
    public function index(Request $request)
    {
        $query = Talent::with(['genres', 'media']);

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

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'talents' => $talents->items(),
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
        $talent = Talent::with(['genres', 'media'])->find($id);

        if (!$talent) {
            return $this->errorResponse('Talent tidak ditemukan', null, 404);
        }

        return $this->successResponse('OK', $talent);
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
            return $this->errorResponse('Hanya talent yang bisa membuat profil', null, 403);
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
            return $this->errorResponse('Validasi gagal', $validator->errors(), 422);
        }

        // Talent should only have one profile per user
        $existingTalent = Talent::where('user_id', $request->user()->id)->first();
        if ($existingTalent) {
            return $this->errorResponse('Profil talent sudah ada', null, 422);
        }

        $talent = Talent::create([
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

        $talent->load('genres');

        return $this->successResponse('Profil talent berhasil dibuat', $talent, 201);
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
            return $this->errorResponse('Talent tidak ditemukan', null, 404);
        }

        // Only owner or admin can update
        if ($request->user()->id !== $talent->user_id && $request->user()->role !== 'admin') {
            return $this->errorResponse('Akses ditolak', null, 403);
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
            return $this->errorResponse('Validasi gagal', $validator->errors(), 422);
        }

        $talent->update($request->only([
            'stage_name', 'price_min', 'price_max', 'city', 'bio', 'portfolio_link'
        ]));

        if ($request->has('genre_ids')) {
            $talent->genres()->sync($request->genre_ids);
        }

        $talent->load('genres');

        return $this->successResponse('Profil talent berhasil diperbarui', $talent);
    }

    #[OA\Delete(path: "/talents/{id}", summary: "Delete talent profile", security: [["bearerAuth" => []]], tags: ["Talent Profile"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Profil talent berhasil dihapus")]
    public function destroy(Request $request, $id)
    {
        $talent = Talent::find($id);

        if (!$talent) {
            return $this->errorResponse('Talent tidak ditemukan', null, 404);
        }

        if ($request->user()->role !== 'admin') {
            return $this->errorResponse('Akses ditolak. Hanya admin yang dapat menghapus data ini.', null, 403);
        }

        $talent->delete();

        return $this->successResponse('Profil talent berhasil dihapus');
    }

    #[OA\Post(path: "/talents/{id}/media", summary: "Upload media portfolio", security: [["bearerAuth" => []]], tags: ["Talent Profile"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(required: true, content: new OA\MediaType(
        mediaType: "multipart/form-data",
        schema: new OA\Schema(
            properties: [
                new OA\Property(property: "file", type: "string", format: "binary"),
                new OA\Property(property: "type", type: "string", enum: ["image", "video", "audio"])
            ]
        )
    ))]
    #[OA\Response(response: 201, description: "Media berhasil diunggah")]
    public function uploadMedia(Request $request, $id)
    {
        $talent = Talent::find($id);

        if (!$talent) {
            return $this->errorResponse('Talent tidak ditemukan', null, 404);
        }

        if ($request->user()->id !== $talent->user_id) {
            return $this->errorResponse('Akses ditolak', null, 403);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file', // Could add mimes:jpg,png,mp4,mp3 depending on requirements
            'type' => 'required|in:image,video,audio',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', $validator->errors(), 422);
        }

        // Mocking file upload for now, ideally use Storage::put
        // e.g., $path = $request->file('file')->store('media', 'public');
        // $url = url('storage/' . $path);
        
        $fileName = $request->file('file')->getClientOriginalName();
        $url = 'https://storage.caritalent.id/media/' . time() . '_' . $fileName;

        $media = Media::create([
            'talent_id' => $talent->id,
            'media_url' => $url,
            'type' => $request->type,
        ]);

        return $this->successResponse('Media berhasil diunggah', $media, 201);
    }

    #[OA\Delete(path: "/talents/{talent_id}/media/{media_id}", summary: "Delete media portfolio", security: [["bearerAuth" => []]], tags: ["Talent Profile"])]
    #[OA\Parameter(name: "talent_id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "media_id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Media berhasil dihapus")]
    public function deleteMedia(Request $request, $talent_id, $media_id)
    {
        $talent = Talent::find($talent_id);

        if (!$talent) {
            return $this->errorResponse('Talent tidak ditemukan', null, 404);
        }

        if ($request->user()->id !== $talent->user_id && $request->user()->role !== 'admin') {
            return $this->errorResponse('Akses ditolak', null, 403);
        }

        $media = Media::where('talent_id', $talent_id)->find($media_id);

        if (!$media) {
            return $this->errorResponse('Media tidak ditemukan', null, 404);
        }
        
        // Logic to delete actual file could be placed here
        $media->delete();

        return $this->successResponse('Media berhasil dihapus');
    }
}
