<?php

namespace TTU\Charon\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

class TesterCallbackRequest extends FormRequest
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
            'charon_id' => 'required|integer',
            'uni_id' => 'required',
            'git_timestamp' => 'required',
            'git_hash' => 'required',
            'git_commit_message' => 'required',
        ];
    }

    /**
     * @param Validator $validator
     */
    public function withValidator($validator)
    {
        if ($validator->fails()) {
            Log::notice('Tester callback with incorrect parameters.', [
                'url' => $this->fullUrl(),
                'body' => $this->all()
            ]);
        }
    }
}
