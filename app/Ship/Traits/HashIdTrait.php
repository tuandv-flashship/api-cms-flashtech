<?php

namespace App\Ship\Traits;

trait HashIdTrait
{
    /**
     * Hash ID helper for Transformers, Tasks, and Actions
     */
    protected function hashId(int|string|null $id): int|string|null
    {
        if ($id === null) {
            return null;
        }

        // If it's a numeric value (int or string representation of int)
        if (is_numeric($id)) {
            $intId = (int) $id;
            if ($intId <= 0) {
                return $intId;
            }

            return config('apiato.hash-id') ? hashids()->encodeOrFail($intId) : $intId;
        }

        // Return non-numeric IDs as is (e.g. UUIDs or already hashed strings)
        return $id;
    }
}
