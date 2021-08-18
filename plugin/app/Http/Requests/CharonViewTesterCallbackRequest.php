<?php

namespace TTU\Charon\Http\Requests;

class CharonViewTesterCallbackRequest extends TesterCallbackRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'slug' => 'required',
            'uniid' => 'required',
            'timestamp' => 'required',
            'hash' => 'required',
        ];
    }
}
