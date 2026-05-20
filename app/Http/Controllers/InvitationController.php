<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Event;
use App\Http\Requests\StoreInvitationRequest;
use App\Http\Requests\RespondInvitationRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class InvitationController extends Controller
{
    use ApiResponse;

    #[OA\Post(
        path: "/invitations",
        summary: "Send Invitation (EO mengundang Talent)",
        description: "EO mengundang talent secara langsung ke event. Sistem otomatis membuat record di tabel applications dengan source=invitation dan status=pending. Akses: EO.",
        security: [["bearerAuth" => []]],
        tags: ["Invitation"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["event_id", "talent_id", "offered_price"],
            properties: [
                new OA\Property(property: "event_id", type: "integer", description: "ID event yang ingin diisi talent ini", example: 1),
                new OA\Property(property: "talent_id", type: "integer", description: "ID user talent yang diundang", example: 3),
                new OA\Property(property: "offered_price", type: "number", description: "Harga yang ditawarkan EO kepada talent", example: 2000000),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Undangan berhasil dikirim",
        content: new OA\JsonContent(example: [
            "success" => true,
            "message" => "Undangan berhasil dikirim",
            "data" => [
                "id" => 15,
                "event_id" => 1,
                "talent_id" => 3,
                "offered_price" => 2000000,
                "status" => "pending",
                "created_at" => "2026-03-08T12:00:00Z"
            ]
        ])
    )]
    #[OA\Response(
        response: 422,
        description: "Talent sudah memiliki lamaran aktif untuk event ini",
        content: new OA\JsonContent(example: ["success" => false, "message" => "Talent ini sudah memiliki lamaran aktif untuk event tersebut"])
    )]
    public function store(StoreInvitationRequest $request)
    {
        $event = Event::find($request->event_id);

        $existing = Application::where('event_id', $request->event_id)
            ->where('talent_id', $request->talent_id)
            ->exists();

        if ($existing) {
            return $this->errorResponse('Talent ini sudah memiliki lamaran aktif untuk event tersebut', 422);
        }

        $data = $request->validated();
        $data['source'] = 'invitation';
        $data['status'] = 'pending';

        $invitation = Application::create($data);

        \App\Models\Notification::create([
            'user_id' => $invitation->talent_id,
            'title' => 'Undangan Manggung Baru',
            'body' => 'Kamu diundang untuk tampil di event ' . $event->title . '.',
            'type' => 'invitation',
            'action' => 'invitation_received',
            'reference_type' => 'invitation',
            'reference_id' => $invitation->id,
            'data' => [
                'invitation_id' => $invitation->id,
                'event_id' => $event->id,
                'event_title' => $event->title,
                'organizer_id' => $event->organizer_id,
                'offered_price' => $invitation->offered_price,
            ],
        ]);

        return $this->successResponse($invitation, 'Undangan berhasil dikirim', 201);
    }

    #[OA\Get(
        path: "/invitations/my",
        summary: "Get My Invitations (Talent melihat undangan masuk)",
        description: "Talent melihat semua undangan yang diterimanya dari EO. Akses: Talent.",
        security: [["bearerAuth" => []]],
        tags: ["Invitation"]
    )]
    #[OA\Response(
        response: 200,
        description: "Daftar undangan talent berhasil diambil",
        content: new OA\JsonContent(example: [
            "success" => true,
            "message" => "OK",
            "data" => [
                "invitations" => [[
                    "id" => 15,
                    "event" => [
                        "id" => 1,
                        "title" => "Punk Night Vol. 3",
                        "event_date" => "2026-04-15",
                        "venue_name" => "Kafe Kota Bandung",
                        "city" => "Bandung",
                        "budget" => 3000000,
                        "latitude" => -6.9175,
                        "longitude" => 107.6191
                    ],
                    "organizer_name" => "Kafe Kota",
                    "offered_price" => 2000000,
                    "status" => "pending",
                    "created_at" => "2026-03-08T12:00:00Z"
                ]]
            ]
        ])
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated",
        content: new OA\JsonContent(example: ["message" => "Unauthenticated."])
    )]
    public function myInvitations()
    {
        $talentId = auth()->id() ?? 1;
        $invitations = Application::where('talent_id', $talentId)
            ->where('source', 'invitation')
            ->with('event')
            ->get();

        return $this->successResponse(['invitations' => $invitations]);
    }

    #[OA\Put(
        path: "/invitations/{id}/respond",
        summary: "Accept / Reject Invitation (Talent)",
        description: "Talent menerima atau menolak undangan dari EO. Jika diterima (accepted), sistem otomatis membuat record Booking baru dengan agreed_price = offered_price. Status hanya boleh: accepted atau rejected. Akses: Talent (penerima undangan).",
        security: [["bearerAuth" => []]],
        tags: ["Invitation"]
    )]
    #[OA\Parameter(name: "id", in: "path", description: "ID undangan (invitation/application)", required: true, schema: new OA\Schema(type: "integer", example: 15))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["status"],
            properties: [
                new OA\Property(property: "status", type: "string", enum: ["accepted","rejected"], description: "Keputusan talent terhadap undangan", example: "accepted"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Undangan diterima (booking dibuat) atau ditolak",
        content: new OA\JsonContent(example: [
            "success" => true,
            "message" => "Undangan diterima dan booking telah dibuat",
            "data" => [
                "invitation" => ["id" => 15, "status" => "accepted"],
                "booking" => ["id" => 6, "application_id" => 15, "agreed_price" => 2000000, "status" => "confirmed"]
            ]
        ])
    )]
    #[OA\Response(
        response: 404,
        description: "Undangan tidak ditemukan",
        content: new OA\JsonContent(example: ["success" => false, "message" => "Undangan tidak ditemukan"])
    )]
    public function respond(RespondInvitationRequest $request, $id)
    {
        $invitation = Application::where('id', $id)
            ->where('source', 'invitation')
            ->with('event')
            ->first();

        if (!$invitation) {
            return $this->errorResponse('Undangan tidak ditemukan', 404);
        }

        if ($invitation->status !== 'pending') {
            return $this->errorResponse('Undangan sudah direspons sebelumnya', 422);
        }

        $invitation->update([
            'status' => $request->status,
        ]);

        $data = ['invitation' => ['id' => $invitation->id, 'status' => $invitation->status]];

        if ($request->status === 'accepted') {
            $booking = \App\Models\Booking::create([
                'application_id' => $invitation->id,
                'agreed_price' => $invitation->offered_price,
                'status' => 'confirmed'
            ]);

            $data['booking'] = [
                'id' => $booking->id,
                'application_id' => $booking->application_id,
                'agreed_price' => $booking->agreed_price,
                'status' => $booking->status
            ];
            $message = 'Undangan diterima dan booking telah dibuat';

            \App\Models\Notification::create([
                'user_id' => $invitation->event->organizer_id,
                'title' => 'Undangan Diterima',
                'body' => 'Talent menerima undangan untuk event ' . $invitation->event->title . '.',
                'type' => 'invitation',
                'action' => 'invitation_accepted',
                'reference_type' => 'booking',
                'reference_id' => $booking->id,
                'data' => [
                    'invitation_id' => $invitation->id,
                    'booking_id' => $booking->id,
                    'event_id' => $invitation->event_id,
                    'event_title' => $invitation->event->title,
                    'talent_id' => $invitation->talent_id,
                    'agreed_price' => $booking->agreed_price,
                ],
            ]);
        } else {
            $message = 'Undangan berhasil ditolak';

            \App\Models\Notification::create([
                'user_id' => $invitation->event->organizer_id,
                'title' => 'Undangan Ditolak',
                'body' => 'Talent menolak undangan untuk event ' . $invitation->event->title . '.',
                'type' => 'invitation',
                'action' => 'invitation_rejected',
                'reference_type' => 'invitation',
                'reference_id' => $invitation->id,
                'data' => [
                    'invitation_id' => $invitation->id,
                    'event_id' => $invitation->event_id,
                    'event_title' => $invitation->event->title,
                    'talent_id' => $invitation->talent_id,
                ],
            ]);
        }

        return $this->successResponse($data, $message);
    }

    #[OA\Get(
        path: "/invitations/sent",
        summary: "Get Sent Invitations (EO melihat undangan yang dikirim)",
        description: "EO melihat semua undangan yang pernah dikirimkannya ke talent. Akses: EO.",
        security: [["bearerAuth" => []]],
        tags: ["Invitation"]
    )]
    #[OA\Response(
        response: 200,
        description: "Daftar undangan terkirim berhasil diambil",
        content: new OA\JsonContent()
    )]
    public function sentInvitations()
    {
        $organizerId = auth()->id() ?? 1;
        $invitations = Application::where('source', 'invitation')
            ->whereHas('event', function ($query) use ($organizerId) {
                $query->where('organizer_id', $organizerId);
            })
            ->with(['event', 'talent'])
            ->latest()
            ->get()->map(function ($app) {
                $talentProfile = \App\Models\Talent::with('genres')->where('user_id', $app->talent_id)->first();
                $user = \App\Models\User::find($app->talent_id);
                return [
                    'id' => $app->id,
                    'event' => $app->event,
                    'talent' => [
                        'id' => $app->talent_id,
                        'stage_name' => $talentProfile ? $talentProfile->stage_name : ($user ? $user->name : 'Unknown'),
                        'city' => $talentProfile ? $talentProfile->city : 'Unknown',
                        'verified' => $talentProfile ? (bool)$talentProfile->verified : false,
                        'average_rating' => $talentProfile ? (float)$talentProfile->average_rating : 0.0,
                        'genre' => $talentProfile ? $talentProfile->genres->pluck('name')->toArray() : [],
                    ],
                    'offered_price' => $app->offered_price,
                    'proposed_price' => $app->proposed_price ?? $app->offered_price,
                    'status' => $app->status,
                    'created_at' => $app->created_at,
                ];
            });

        return $this->successResponse(['invitations' => $invitations]);
    }
}
