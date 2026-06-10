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
        // Cari event berdasarkan ID yang dikirim
        $event = Event::find($request->event_id);

        // Pastikan event tersebut ada
        if (!$event) {
            return $this->errorResponse('Event tidak ditemukan', 404);
        }

        // Ambil ID talent yang sedang login
        $talentId = auth()->id() ?? 1;

        // Cek apakah talent ini sudah pernah melamar ke event yang sama
        $existing = Application::where('event_id', $request->event_id)
            ->where('talent_id', $talentId)
            ->exists();

        // Tolak jika sudah ada lamaran sebelumnya
        if ($existing) {
            return $this->errorResponse('Kamu sudah pernah melamar ke event ini', 422);
        }

        // Siapkan data lamaran dengan source dan status default
        $data = $request->validated();
        $data['talent_id'] = $talentId;
        $data['source'] = 'apply';
        $data['status'] = 'pending';

        // Simpan lamaran ke database
        $application = Application::create($data);

        // Kirim notifikasi ke EO bahwa ada lamaran baru masuk
        \App\Models\Notification::create([
            'user_id' => $event->organizer_id,
            'title' => 'Lamaran Baru Masuk',
            'body' => auth()->user()->name . ' melamar untuk event ' . $event->title . '.',
            'type' => 'application',
            'action' => 'application_created',
            'reference_type' => 'application',
            'reference_id' => $application->id,
            'data' => [
                'application_id' => $application->id,
                'event_id' => $event->id,
                'event_title' => $event->title,
                'talent_id' => $talentId,
                'talent_name' => auth()->user()->name,
            ],
        ]);

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
        // Cari event berdasarkan ID dari URL
        $event = Event::find($eventId);
        if (!$event) {
            return $this->errorResponse('Event tidak ditemukan', 404);
        }

        // Pastikan hanya pemilik event yang bisa melihat daftar pelamar
        if ($event->organizer_id != auth()->id()) {
            return $this->errorResponse('Akses ditolak. Anda bukan penyelenggara event ini', 403);
        }

        // Query semua lamaran yang masuk ke event ini
        $query = Application::where('event_id', $eventId);

        // Filter berdasarkan status jika ada parameter yang dikirim
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        // Filter berdasarkan sumber lamaran jika ada
        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        $applications = $query->latest()->get()->map(function ($app) {
            $talentProfile = \App\Models\Talent::with('genres')->where('user_id', $app->talent_id)->first();
            $user = \App\Models\User::find($app->talent_id);
            
            return [
                'id' => $app->id,
                'source' => $app->source,
                'message' => $app->message,
                'proposed_price' => $app->proposed_price,
                'status' => $app->status,
                'created_at' => $app->created_at,
                'talent' => [
                    'id' => $app->talent_id,
                    'name' => $user ? $user->name : 'Unknown',
                    'email' => $user ? $user->email : '',
                    'phone' => $user ? $user->phone : '',
                    'stage_name' => $talentProfile ? $talentProfile->stage_name : ($user ? $user->name : 'Unknown'),
                    'genre' => $talentProfile ? $talentProfile->genres->pluck('name')->toArray() : [],
                    'city' => $talentProfile ? $talentProfile->city : 'Unknown',
                    'verified' => $talentProfile ? (bool)$talentProfile->verified : false,
                    'average_rating' => $talentProfile ? (float)$talentProfile->average_rating : 0.0,
                    'bio' => $talentProfile ? $talentProfile->bio : '',
                    'portfolio_link' => $talentProfile ? $talentProfile->portfolio_link : '',
                    'price_min' => $talentProfile ? $talentProfile->price_min : null,
                    'price_max' => $talentProfile ? $talentProfile->price_max : null,
                ]
            ];
        });

        return $this->successResponse([
            'event' => $event,
            'applications' => $applications
        ]);
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
        // Ambil semua lamaran milik talent yang sedang login, hanya yang sumber-nya apply
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
        response: 422,
        description: "Event tidak sedang dibuka / Lamaran sudah direspons sebelumnya",
        content: new OA\JsonContent(example: ["success" => false, "message" => "Lamaran tidak dapat direspons karena status event bukan buka"])
    )]
    #[OA\Response(
        response: 404,
        description: "Lamaran tidak ditemukan",
        content: new OA\JsonContent(example: ["success" => false, "message" => "Lamaran tidak ditemukan"])
    )]
    public function updateStatus(UpdateApplicationStatusRequest $request, $id)
    {
        // Cari lamaran beserta data event-nya sekaligus
        $application = Application::with('event')->find($id);

        // Pastikan lamaran tersebut ada
        if (!$application) {
            return $this->errorResponse('Lamaran tidak ditemukan', 404);
        }

        // Pastikan yang mengubah status adalah EO pemilik event
        if ($application->event->organizer_id != auth()->id()) {
            return $this->errorResponse('Akses ditolak. Anda bukan penyelenggara event ini', 403);
        }

        // Lamaran yang sudah direspons tidak bisa diubah lagi
        if ($application->status !== 'pending') {
            return $this->errorResponse('Lamaran sudah direspons sebelumnya', 422);
        }

        // Hanya event yang masih berstatus dibuka yang bisa menerima respons
        if ($application->event->status !== 'dibuka') {
            return $this->errorResponse('Lamaran tidak dapat direspons karena status event bukan buka', 422);
        }

        // Update status lamaran sesuai keputusan EO
        $application->update([
            'status' => $request->status,
        ]);

        $data = ['application' => ['id' => $application->id, 'status' => $application->status]];

        // Jika diterima, buat booking baru secara otomatis
        if ($request->status === 'accepted') {
            $booking = \App\Models\Booking::create([
                'application_id' => $application->id,
                'agreed_price' => $request->agreed_price,
                'status' => 'confirmed'
            ]);

            $data['booking'] = [
                'id' => $booking->id,
                'application_id' => $booking->application_id,
                'agreed_price' => $booking->agreed_price,
                'status' => $booking->status
            ];

            // Beritahu talent bahwa lamarannya diterima
            \App\Models\Notification::create([
                'user_id' => $application->talent_id,
                'title' => 'Lamaran Diterima',
                'body' => 'Lamaran kamu untuk event ' . $application->event->title . ' diterima.',
                'type' => 'application',
                'action' => 'application_accepted',
                'reference_type' => 'booking',
                'reference_id' => $booking->id,
                'data' => [
                    'application_id' => $application->id,
                    'booking_id' => $booking->id,
                    'event_id' => $application->event_id,
                    'event_title' => $application->event->title,
                    'agreed_price' => $booking->agreed_price,
                ],
            ]);

            return $this->successResponse($data, 'Lamaran diterima dan booking telah dibuat');
        }

        // Jika ditolak, kirim notifikasi penolakan ke talent
        \App\Models\Notification::create([
            'user_id' => $application->talent_id,
            'title' => 'Lamaran Ditolak',
            'body' => 'Lamaran kamu untuk event ' . $application->event->title . ' ditolak.',
            'type' => 'application',
            'action' => 'application_rejected',
            'reference_type' => 'application',
            'reference_id' => $application->id,
            'data' => [
                'application_id' => $application->id,
                'event_id' => $application->event_id,
                'event_title' => $application->event->title,
            ],
        ]);

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
        // Cari lamaran milik talent yang ingin dibatalkan
        $application = Application::with('event')->find($id);

        // Pastikan lamaran tersebut ada
        if (!$application) {
            return $this->errorResponse('Lamaran tidak ditemukan', 404);
        }

        // Lamaran yang sudah direspons tidak bisa dibatalkan
        if ($application->status !== 'pending') {
            return $this->errorResponse('Hanya lamaran pending yang bisa dibatalkan', 422);
        }

        // Beritahu EO bahwa talent membatalkan lamarannya
        \App\Models\Notification::create([
            'user_id' => $application->event->organizer_id,
            'title' => 'Lamaran Dibatalkan',
            'body' => 'Talent membatalkan lamaran untuk event ' . $application->event->title . '.',
            'type' => 'application',
            'action' => 'application_cancelled',
            'reference_type' => 'event',
            'reference_id' => $application->event_id,
            'data' => [
                'application_id' => $application->id,
                'event_id' => $application->event_id,
                'event_title' => $application->event->title,
                'talent_id' => $application->talent_id,
            ],
        ]);

        // Hapus data lamaran dari database
        $application->delete();

        return $this->successResponse(null, 'Lamaran berhasil dibatalkan');
    }
}
