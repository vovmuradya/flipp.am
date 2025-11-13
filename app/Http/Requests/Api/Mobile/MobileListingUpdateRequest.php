<?php

namespace App\Http\Requests\Api\Mobile;

class MobileListingUpdateRequest extends MobileListingStoreRequest
{
    public function rules(): array
    {
        return $this->baseRules(true);
    }
}
