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

    #[OA\Post(
        path: "/applications",
        summary: "Apply ke Event (Talent)",
        description: "Talent mendaftar / melamar ke sebuah event. Sistem otomatis men-set source=apply dan status=pending. Satu talent hanya bisa melamar sekali ke event yang sama. Akses: Talent.",
        security: [["bearerAuth" => []]],
        tags: ["Application"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["event_id", "proposed_price"],
            properties: [
                new OA\Property(property: "event_id", type: "integer", description: "ID event yang ingin dilamar", example: 1),
                new OA\Property(property: "message", type: "string", description: "Pesan pengantar lamaran (opsional)", example: "Kami band pop punk dari Bandung dengan pengalaman 5 tahun, siap tampil di acara ini."),
                new OA\Property(property: "proposed_price", type: "number", description: "Harga yang ditawarkan talent", example: 1500000),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Lamaran berhasil dikirim",
        content: new OA\JsonContent(example: [
            "success" => true,
            "message" => "Lamaran berhasil dikirim",
            "data" => [
                "id" => 10,
                "event_id" => 1,
                "talent_id" => 3,
                "source" => "apply",
                "message" => "Kami band pop punk dari Bandung dengan pengalaman 5 tahun, siap tampil di acara ini.",
                "proposed_price" => 1500000,
                "status" => "pending",
                "created_at" => "2026-03-08T11:00:00Z"
            ]
        ])
    )]
    #[OA\Response(
        response: 422,
        description: "Sudah pernah melamar ke event ini",
        content: new OA\JsonContent(example: ["success" => false, "message" => "Kamu sudah pernah melamar ke event ini"])
    )]
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

    #[OA\Get(
        path: "/events/{event_id}/applications",
        summary: "Get Applications by Event (EO)",
        description: "EO melihat semua pelamar pada event miliknya. Bisa difilter berdasarkan status dan source. Akses: EO (pemilik event).",
        security: [["bearerAuth" => []]],
        tags: ["Application"]
    )]
    #[OA\Parameter(name: "event_id", in: "path", description: "ID event", required: true, schema: new OA\Schema(type: "integer", example: 1))]
    #[OA\Parameter(name: "status", in: "query", description: "Filter berdasarkan status lamaran", required: false, schema: new OA\Schema(type: "string", enum: ["pending","accepted","rejected"]))]
    #[OA\Parameter(name: "source", in: "query", description: "Filter berdasarkan sumber lamaran", required: false, schema: new OA\Schema(type: "string", enum: ["apply","invitation"]))]
    #[OA\Response(
        response: 200,
        description: "Daftar pelamar berhasil diambil",
        content: new OA\JsonContent(example: [
            "success" => true,
            "message" => "OK",
            "data" => [
                "applications" => [[
                    "id" => 10,
                    "talent" => [
                        "id" => 3,
                        "stage_name" => "The Broken Strings",
                        "genre" => ["Pop Punk"],
                        "city" => "Bandung",
                        "verified" => true,
                        "average_rating" => 4.5
                    ],
                    "source" => "apply",
                    "message" => "Kami band pop punk dari Bandung...",
                    "proposed_price" => 1500000,
                    "status" => "pending",
                    "created_at" => "2026-03-08T11:00:00Z"
                ]]
            ]
        ])
    )]
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

    #[OA\Get(
        path: "/applications/my",
        summary: "Get My Applications (Talent)",
        description: "Talent melihat semua lamaran yang pernah ia kirimkan ke berbagai event. Akses: Talent.",
        security: [["bearerAuth" => []]],
        tags: ["Application"]
    )]
    #[OA\Response(
        response: 200,
        description: "Daftar lamaran talent berhasil diambil",
        content: new OA\JsonContent(example: [
            "success" => true,
            "message" => "OK",
            "data" => [
                "applications" => [[
                    "id" => 10,
                    "event" => [
                        "id" => 1,
                        "title" => "Punk Night Vol. 3",
                        "event_date" => "2026-04-15",
                        "venue_name" => "Kafe Kota Bandung",
                        "city" => "Bandung",
                        "latitude" => -6.9175,
                        "longitude" => 107.6191
                    ],
                    "source" => "apply",
                    "proposed_price" => 1500000,
                    "status" => "pending",
                    "created_at" => "2026-03-08T11:00:00Z"
                ]]
            ]
        ])
    )]
    public function myApplications()
    {
        $talentId = auth()->id() ?? 1;
        $applications = Application::where('talent_id', $talentId)
            ->where('source', 'apply')
            ->with('event')
            ->get();

        return $this->successResponse(['applications' => $applications]);
    }

    #[OA\Put(
        path: "/applications/{id}/status",
        summary: "Accept / Reject Application (EO)",
        description: "EO menerima atau menolak lamaran talent. Jika diterima (accepted), sistem otomatis membuat record Booking baru. Field agreed_price wajib diisi jika status = accepted. Akses: EO (pemilik event).",
        security: [["bearerAuth" => []]],
        tags: ["Application"]
    )]
    #[OA\Parameter(name: "id", in: "path", description: "ID lamaran (application)", required: true, schema: new OA\Schema(type: "integer", example: 10))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["status"],
            properties: [
                new OA\Property(property: "status", type: "string", enum: ["accepted","rejected"], description: "Status keputusan EO", example: "accepted"),
                new OA\Property(property: "agreed_price", type: "number", description: "Harga yang disepakati (wajib diisi jika status=accepted)", example: 1500000),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Lamaran diterima dan booking dibuat / Lamaran ditolak",
        content: new OA\JsonContent(example: [
            "success" => true,
            "message" => "Lamaran diterima dan booking telah dibuat",
            "data" => [
                "application" => ["id" => 10, "status" => "accepted"],
                "booking" => ["id" => 5, "application_id" => 10, "agreed_price" => 1500000, "status" => "confirmed"]
            ]
        ])
    )]
    #[OA\Response(
        response: 404,
        description: "Lamaran tidak ditemukan",
        content: new OA\JsonContent(example: ["success" => false, "message" => "Lamaran tidak ditemukan"])
    )]
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

    #[OA\Delete(
        path: "/applications/{id}",
        summary: "Cancel Application (Talent)",
        description: "Talent membatalkan lamarannya. Hanya bisa dilakukan selama status masih pending. Akses: Talent (pemilik lamaran).",
        security: [["bearerAuth" => []]],
        tags: ["Application"]
    )]
    #[OA\Parameter(name: "id", in: "path", description: "ID lamaran (application)", required: true, schema: new OA\Schema(type: "integer", example: 10))]
    #[OA\Response(
        response: 200,
        description: "Lamaran berhasil dibatalkan",
        content: new OA\JsonContent(example: ["success" => true, "message" => "Lamaran berhasil dibatalkan"])
    )]
    #[OA\Response(
        response: 422,
        description: "Lamaran tidak bisa dibatalkan (status bukan pending)",
        content: new OA\JsonContent(example: ["success" => false, "message" => "Hanya lamaran pending yang bisa dibatalkan"])
    )]
    #[OA\Response(
        response: 404,
        description: "Lamaran tidak ditemukan",
        content: new OA\JsonContent(example: ["success" => false, "message" => "Lamaran tidak ditemukan"])
    )]
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
