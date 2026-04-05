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

    #[OA\Get(path: "/events", summary: "Get All Events", tags: ["Event"])]
    #[OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "city", in: "query", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "budget_min", in: "query", schema: new OA\Schema(type: "number"))]
    #[OA\Parameter(name: "budget_max", in: "query", schema: new OA\Schema(type: "number"))]
    #[OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string"))]
    #[OA\Response(response: 200, description: "OK")]
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

    #[OA\Get(path: "/events/{id}", summary: "Get Event by ID", tags: ["Event"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "OK")]
    public function show($id)
    {
        $event = Event::with('genres')->find($id);

        if (!$event) {
            return $this->errorResponse('Event tidak ditemukan', 404);
        }

        return $this->successResponse($event);
    }

    #[OA\Post(path: "/events", summary: "Create Event", security: [["bearerAuth" => []]], tags: ["Event"])]
    #[OA\Response(response: 201, description: "Event berhasil dibuat")]
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

    #[OA\Put(path: "/events/{id}", summary: "Update Event", security: [["bearerAuth" => []]], tags: ["Event"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Event berhasil diperbarui")]
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

    #[OA\Delete(path: "/events/{id}", summary: "Cancel Event", security: [["bearerAuth" => []]], tags: ["Event"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Event berhasil dibatalkan")]
    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse('Event tidak ditemukan', 404);
        }

        $event->update(['status' => 'cancelled']);

        return $this->successResponse(null, 'Event berhasil dibatalkan');
    }

    #[OA\Get(path: "/events/my", summary: "Get My Events (EO)", security: [["bearerAuth" => []]], tags: ["Event"])]
    #[OA\Response(response: 200, description: "OK")]
    public function myEvents()
    {
        $events = Event::where('organizer_id', auth()->id() ?? 1)->paginate(15);

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
}
