<?php

namespace TTU\Charon\Http\Requests;

class CharonViewTesterCallbackRequest extends TesterCallbackRequest
{

    /** @var int */
    private $status;

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

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return CharonViewTesterCallbackRequest
     */
    public function setStatus(int $status): CharonViewTesterCallbackRequest
    {
        $this->status = $status;
        return $this;
    }


}
