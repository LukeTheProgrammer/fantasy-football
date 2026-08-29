<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DraftFrameStoreRequest extends FormRequest
{
    /**
     * The browser extension posts from the draft room, where it has no session
     * with this app and no way to obtain a token. The app is single user on a
     * LAN, and the endpoint only appends frames to a log, so the post is left
     * open rather than given a credential to carry.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'frames'             => ['required', 'array', 'max:500'],
            'frames.*.direction' => ['required', 'string', 'in:open,close,sent,recv'],
            'frames.*.url'       => ['required', 'string'],
            'frames.*.at'        => ['required', 'integer'],
            'frames.*.encoding'  => ['required', 'string', 'in:text,base64,error'],
            'frames.*.frame'     => ['present', 'string'],
        ];
    }
}
