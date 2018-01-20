<?php

namespace TTU\Charon\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

class GitCallbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'repo' => 'required',
            'user' => 'required',
        ];
    }

    /**
     * Before validating, log the incorrect request.
     *
     * @param Validator $validator
     */
    public function withValidator($validator)
    {
        if ($validator->fails()) {
            Log::notice('Git callback with incorrect parameters', [
                'url' => $this->fullUrl(),
                'body' => $this->all(),
            ]);
        }
    }
}
