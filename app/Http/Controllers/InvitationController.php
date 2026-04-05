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

    #[OA\Post(path: "/invitations", summary: "Send Invitation (EO)", security: [["bearerAuth" => []]], tags: ["Invitation"])]
    #[OA\Response(response: 201, description: "Undangan berhasil dikirim")]
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

    #[OA\Get(path: "/invitations/my", summary: "Get My Invitations (Talent)", security: [["bearerAuth" => []]], tags: ["Invitation"])]
    #[OA\Response(response: 200, description: "OK")]
    public function myInvitations()
    {
        $talentId = auth()->id() ?? 1;
        $invitations = Application::where('talent_id', $talentId)
            ->where('source', 'invitation')
            ->with('event')
            ->get();

        return $this->successResponse(['invitations' => $invitations]);
    }

    #[OA\Put(path: "/invitations/{id}/respond", summary: "Accept / Reject Invitation (Talent)", security: [["bearerAuth" => []]], tags: ["Invitation"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Undangan diterima/ditolak")]
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
