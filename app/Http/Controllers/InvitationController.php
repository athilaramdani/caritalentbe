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
            ->first();

        if (!$invitation) {
            return $this->errorResponse('Undangan tidak ditemukan', 404);
        }

        $invitation->update([
            'status' => $request->status,
        ]);

        $data = ['invitation' => ['id' => $invitation->id, 'status' => $invitation->status]];

        if ($request->status === 'accepted') {
            $data['booking'] = [
                'application_id' => $invitation->id,
                'agreed_price' => $invitation->offered_price,
                'status' => 'confirmed'
            ];
            $message = 'Undangan diterima dan booking telah dibuat';
        } else {
            $message = 'Undangan berhasil ditolak';
        }

        return $this->successResponse($data, $message);
    }
}
