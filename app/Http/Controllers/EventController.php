<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class EventController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: "/events",
        summary: "Get All Events",
        description: "Mendapatkan daftar semua event dengan filter opsional. Akses: Public.",
        tags: ["Event"]
    )]
    #[OA\Parameter(name: "status", in: "query", description: "Filter status event", required: false, schema: new OA\Schema(type: "string", enum: ["dibuka","ditutup","selesai","dibatalkan"]))]
    #[OA\Parameter(name: "genre", in: "query", description: "Filter genre yang dibutuhkan", required: false, schema: new OA\Schema(type: "string", example: "Pop Punk"))]
    #[OA\Parameter(name: "city", in: "query", description: "Filter kota venue", required: false, schema: new OA\Schema(type: "string", example: "Bandung"))]
    #[OA\Parameter(name: "budget_min", in: "query", description: "Filter budget minimum", required: false, schema: new OA\Schema(type: "integer", example: 1000000))]
    #[OA\Parameter(name: "budget_max", in: "query", description: "Filter budget maksimum", required: false, schema: new OA\Schema(type: "integer", example: 5000000))]
    #[OA\Parameter(name: "date_from", in: "query", description: "Filter tanggal mulai (YYYY-MM-DD)", required: false, schema: new OA\Schema(type: "string", format: "date", example: "2026-04-01"))]
    #[OA\Parameter(name: "date_to", in: "query", description: "Filter tanggal akhir (YYYY-MM-DD)", required: false, schema: new OA\Schema(type: "string", format: "date", example: "2026-04-30"))]
    #[OA\Parameter(name: "search", in: "query", description: "Cari berdasarkan judul event", required: false, schema: new OA\Schema(type: "string", example: "Punk Night"))]
    #[OA\Parameter(name: "page", in: "query", description: "Nomor halaman", required: false, schema: new OA\Schema(type: "integer", example: 1))]
    #[OA\Response(
        response: 200,
        description: "Daftar event berhasil diambil",
        content: new OA\JsonContent(example: [
            "success" => true,
            "message" => "OK",
            "data" => [
                "events" => [[
                    "id" => 1,
                    "organizer_id" => 2,
                    "organizer_name" => "Kafe Kota",
                    "title" => "Punk Night Vol. 3",
                    "description" => "Malam punk rock terbaik di Bandung.",
                    "genre_needed" => ["Pop Punk", "Hardcore"],
                    "budget" => 3000000,
                    "event_date" => "2026-04-15",
                    "venue_name" => "Kafe Kota Bandung",
                    "latitude" => -6.9175,
                    "longitude" => 107.6191,
                    "city" => "Bandung",
                    "status" => "dibuka",
                    "created_at" => "2026-03-01T09:00:00Z"
                ]],
                "pagination" => ["current_page" => 1, "per_page" => 15, "total" => 23, "last_page" => 2]
            ]
        ])
    )]
    public function index(Request $request)
    {
        $query = Event::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('city')) {
            $query->where('city', $request->city);
        }
        if ($request->has('budget_min')) {
            $query->where('budget', '>=', $request->budget_min);
        }
        if ($request->has('budget_max')) {
            $query->where('budget', '<=', $request->budget_max);
        }
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $events = $query->paginate($request->get('per_page', 15));

        return $this->successResponse([
            'events' => $events->items(),
            'pagination' => [
                'current_page' => $events->currentPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'last_page' => $events->lastPage()
            ]
        ]);
    }

    #[OA\Get(
        path: "/events/{id}",
        summary: "Get Event by ID",
        description: "Mendapatkan detail event berdasarkan ID. Akses: Public.",
        tags: ["Event"]
    )]
    #[OA\Parameter(name: "id", in: "path", description: "ID event", required: true, schema: new OA\Schema(type: "integer", example: 1))]
    #[OA\Response(
        response: 200,
        description: "Detail event berhasil diambil",
        content: new OA\JsonContent(example: [
            "success" => true,
            "message" => "OK",
            "data" => [
                "id" => 1,
                "organizer_id" => 2,
                "organizer_name" => "Kafe Kota",
                "title" => "Punk Night Vol. 3",
                "description" => "Malam punk rock terbaik di Bandung.",
                "genre_needed" => ["Pop Punk", "Hardcore"],
                "budget" => 3000000,
                "event_date" => "2026-04-15",
                "venue_name" => "Kafe Kota Bandung",
                "latitude" => -6.9175,
                "longitude" => 107.6191,
                "city" => "Bandung",
                "status" => "dibuka",
                "total_applicants" => 5,
                "created_at" => "2026-03-01T09:00:00Z"
            ]
        ])
    )]
    #[OA\Response(
        response: 404,
        description: "Event tidak ditemukan",
        content: new OA\JsonContent(example: ["success" => false, "message" => "Event tidak ditemukan"])
    )]
    public function show($id)
    {
        $event = Event::with('genres')->find($id);

        if (!$event) {
            return $this->errorResponse('Event tidak ditemukan', 404);
        }

        $eventArray = $event->toArray();
        $eventArray['genre_needed'] = $event->genres->pluck('name')->toArray();
        
        // Count applications manually or use withCount('applications')
        $eventArray['total_applicants'] = \App\Models\Application::where('event_id', $id)->count();

        return $this->successResponse($eventArray);
    }

    #[OA\Post(
        path: "/events",
        summary: "Create Event",
        description: "Membuat event baru. Akses: EO (Event Organizer).",
        security: [["bearerAuth" => []]],
        tags: ["Event"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["title", "description", "budget", "event_date", "venue_name", "city"],
            properties: [
                new OA\Property(property: "title", type: "string", example: "Punk Night Vol. 3"),
                new OA\Property(property: "description", type: "string", example: "Malam punk rock terbaik di Bandung."),
                new OA\Property(property: "genre_ids", type: "array", items: new OA\Items(type: "integer"), example: [1, 5]),
                new OA\Property(property: "budget", type: "number", example: 3000000),
                new OA\Property(property: "event_date", type: "string", format: "date", example: "2026-04-15"),
                new OA\Property(property: "venue_name", type: "string", example: "Kafe Kota Bandung"),
                new OA\Property(property: "latitude", type: "number", format: "float", example: -6.9175),
                new OA\Property(property: "longitude", type: "number", format: "float", example: 107.6191),
                new OA\Property(property: "city", type: "string", example: "Bandung"),
                new OA\Property(property: "status", type: "string", enum: ["dibuka","ditutup","selesai","dibatalkan"], example: "dibuka"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Event berhasil dibuat",
        content: new OA\JsonContent(example: [
            "success" => true,
            "message" => "Event berhasil dibuat",
            "data" => ["id" => 1, "title" => "Punk Night Vol. 3", "status" => "dibuka", "created_at" => "2026-03-08T10:00:00Z"]
        ])
    )]
    #[OA\Response(
        response: 422,
        description: "Validasi gagal",
        content: new OA\JsonContent(example: ["success" => false, "message" => "Validasi gagal", "errors" => ["title" => ["The title field is required."]]])
    )]
    public function store(StoreEventRequest $request)
    {
        $data = $request->validated();
        $data['organizer_id'] = auth()->id() ?? 1;

        $event = Event::create($data);

        if ($request->has('genre_ids')) {
            $event->genres()->sync($request->genre_ids);
        }

        return $this->successResponse($event, 'Event berhasil dibuat', 201);
    }

    #[OA\Put(
        path: "/events/{id}",
        summary: "Update Event",
        description: "Memperbarui data event. Akses: EO (pemilik event).",
        security: [["bearerAuth" => []]],
        tags: ["Event"]
    )]
    #[OA\Parameter(name: "id", in: "path", description: "ID event", required: true, schema: new OA\Schema(type: "integer", example: 1))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "title", type: "string", example: "Punk Night Vol. 3 - Special Edition"),
                new OA\Property(property: "budget", type: "number", example: 4000000),
                new OA\Property(property: "status", type: "string", enum: ["dibuka","ditutup","selesai","dibatalkan"], example: "dibuka"),
                new OA\Property(property: "genre_ids", type: "array", items: new OA\Items(type: "integer"), example: [1, 2, 4]),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Event berhasil diperbarui",
        content: new OA\JsonContent(example: ["success" => true, "message" => "Event berhasil diperbarui", "data" => ["id" => 1, "title" => "Punk Night Vol. 3 - Special Edition", "status" => "dibuka"]])
    )]
    #[OA\Response(
        response: 404,
        description: "Event tidak ditemukan",
        content: new OA\JsonContent(example: ["success" => false, "message" => "Event tidak ditemukan"])
    )]
    public function update(UpdateEventRequest $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse('Event tidak ditemukan', 404);
        }

        $event->update($request->validated());

        if ($request->has('genre_ids')) {
            $event->genres()->sync($request->genre_ids);
        }

        return $this->successResponse($event, 'Event berhasil diperbarui');
    }

    #[OA\Delete(
        path: "/events/{id}",
        summary: "Cancel Event",
        description: "Membatalkan event (soft cancel — status berubah menjadi 'dibatalkan', tidak dihapus permanen). Akses: EO (pemilik), Admin.",
        security: [["bearerAuth" => []]],
        tags: ["Event"]
    )]
    #[OA\Parameter(name: "id", in: "path", description: "ID event", required: true, schema: new OA\Schema(type: "integer", example: 1))]
    #[OA\Response(
        response: 200,
        description: "Event berhasil dibatalkan",
        content: new OA\JsonContent(example: ["success" => true, "message" => "Event berhasil dibatalkan"])
    )]
    #[OA\Response(
        response: 404,
        description: "Event tidak ditemukan",
        content: new OA\JsonContent(example: ["success" => false, "message" => "Event tidak ditemukan"])
    )]
    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse('Event tidak ditemukan', 404);
        }

        $event->update(['status' => 'dibatalkan']);

        // Ambil semua ID talent yang pernah melamar atau diundang ke event ini
        $talentIds = \App\Models\Application::where('event_id', $event->id)
            ->distinct()
            ->pluck('talent_id');

        // Kirim notifikasi ke masing-masing talent
        foreach ($talentIds as $talentId) {
            \App\Models\Notification::create([
                'user_id' => $talentId,
                'title' => 'Event Dibatalkan',
                'body' => 'Mohon maaf, event "' . $event->title . '" telah dibatalkan oleh pihak penyelenggara.',
                'type' => 'event',
                'action' => 'event_cancelled',
                'reference_type' => 'event',
                'reference_id' => $event->id,
                'data' => [
                    'event_id' => $event->id,
                    'event_title' => $event->title,
                ],
            ]);
        }

        return $this->successResponse(null, 'Event berhasil dibatalkan');
    }

    #[OA\Get(
        path: "/events/my",
        summary: "Get My Events (EO)",
        description: "Mendapatkan daftar event milik EO yang sedang login. Akses: EO.",
        security: [["bearerAuth" => []]],
        tags: ["Event"]
    )]
    #[OA\Response(
        response: 200,
        description: "Daftar event milik EO",
        content: new OA\JsonContent(example: [
            "success" => true,
            "message" => "OK",
            "data" => [
                "events" => [[
                    "id" => 1,
                    "title" => "Punk Night Vol. 3",
                    "status" => "dibuka",
                    "event_date" => "2026-04-15",
                    "city" => "Bandung",
                    "budget" => 3000000,
                ]],
                "pagination" => ["current_page" => 1, "per_page" => 15, "total" => 3, "last_page" => 1]
            ]
        ])
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated",
        content: new OA\JsonContent(example: ["message" => "Unauthenticated."])
    )]
    public function myEvents()
    {
        $events = Event::with(['genres'])->withCount('applications')->where('organizer_id', auth()->id() ?? 1)->latest()->paginate(15);

        $mappedEvents = collect($events->items())->map(function ($event) {
            $eventArray = $event->toArray();
            $eventArray['total_applicants'] = $event->applications_count;
            $eventArray['genre_needed'] = $event->genres->pluck('name')->toArray();
            return $eventArray;
        });

        return $this->successResponse([
            'events' => $mappedEvents,
            'pagination' => [
                'current_page' => $events->currentPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'last_page' => $events->lastPage()
            ]
        ]);
    }
}
