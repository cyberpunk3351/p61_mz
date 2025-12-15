<?php

namespace App\Http\Requests\Playlist;

use App\Models\Playlist;
use App\Models\Track;
use Illuminate\Foundation\Http\FormRequest;

class DetachTrackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $playlist = $this->route('playlist');
        $track = $this->route('track');

        return $this->user() !== null
            && $playlist instanceof Playlist
            && $track instanceof Track
            && $playlist
                ->tracks()
                ->whereKey($track->getKey())
                ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
