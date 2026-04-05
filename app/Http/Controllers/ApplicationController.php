<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Event;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ApplicationController extends Controller
{
    use ApiResponse;

    #[OA\Post(path: "/applications", summary: "Apply ke Event (Talent)", security: [["bearerAuth" => []]], tags: ["Application"])]
    #[OA\Response(response: 201, description: "Lamaran berhasil dikirim")]
    public function store(StoreApplicationRequest $request)
    {
        $event = Event::find($request->event_id);
        
        if (!$event) {
            return $this->errorResponse('Event tidak ditemukan', 404);
        }

        $talentId = auth()->id() ?? 1;

        $existing = Application::where('event_id', $request->event_id)
            ->where('talent_id', $talentId)
            ->exists();

        if ($existing) {
            return $this->errorResponse('Kamu sudah pernah melamar ke event ini', 422);
        }

        $data = $request->validated();
        $data['talent_id'] = $talentId;
        $data['source'] = 'apply';
        $data['status'] = 'pending';

        $application = Application::create($data);

        return $this->successResponse($application, 'Lamaran berhasil dikirim', 201);
    }

    #[OA\Get(path: "/events/{event_id}/applications", summary: "Get Applications by Event (EO)", security: [["bearerAuth" => []]], tags: ["Application"])]
    #[OA\Parameter(name: "event_id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "OK")]
    public function indexByEvent(Request $request, $eventId)
    {
        $query = Application::where('event_id', $eventId);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        $applications = $query->with('talent')->get();

        return $this->successResponse(['applications' => $applications]);
    }

    #[OA\Get(path: "/applications/my", summary: "Get My Applications (Talent)", security: [["bearerAuth" => []]], tags: ["Application"])]
    #[OA\Response(response: 200, description: "OK")]
    public function myApplications()
    {
        $talentId = auth()->id() ?? 1;
        $applications = Application::where('talent_id', $talentId)
            ->where('source', 'apply')
            ->with('event')
            ->get();

        return $this->successResponse(['applications' => $applications]);
    }

    #[OA\Put(path: "/applications/{id}/status", summary: "Accept / Reject Application (EO)", security: [["bearerAuth" => []]], tags: ["Application"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Lamaran status diperbarui")]
    public function updateStatus(UpdateApplicationStatusRequest $request, $id)
    {
        $application = Application::find($id);

        if (!$application) {
            return $this->errorResponse('Lamaran tidak ditemukan', 404);
        }

        $application->update([
            'status' => $request->status,
        ]);
        
        $data = ['application' => ['id' => $application->id, 'status' => $application->status]];
        
        if ($request->status === 'accepted') {
             $data['booking'] = [
                 'application_id' => $application->id,
                 'agreed_price' => $request->agreed_price,
                 'status' => 'confirmed'
             ];
             return $this->successResponse($data, 'Lamaran diterima dan booking telah dibuat');
        }

        return $this->successResponse($data, 'Lamaran ditolak');
    }

    #[OA\Delete(path: "/applications/{id}", summary: "Cancel Application (Talent)", security: [["bearerAuth" => []]], tags: ["Application"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Lamaran berhasil dibatalkan")]
    public function destroy($id)
    {
        $application = Application::find($id);

        if (!$application) {
            return $this->errorResponse('Lamaran tidak ditemukan', 404);
        }

        if ($application->status !== 'pending') {
            return $this->errorResponse('Hanya lamaran pending yang bisa dibatalkan', 422);
        }

        $application->delete();

        return $this->successResponse(null, 'Lamaran berhasil dibatalkan');
    }
}
