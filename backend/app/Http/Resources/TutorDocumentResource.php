<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TutorDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'type'          => $this->type,
            'file_name'     => $this->file_name,
            'file_size'     => $this->file_size,
            'mime_type'     => $this->mime_type,
            'review_status' => $this->review_status,
            'file_url'      => $this->file_path
                ? rtrim(config('app.url'), '/') . '/private-storage/' . strtr(base64_encode($this->file_path), '+/', '-_')
                : null,
        ];
    }
}
