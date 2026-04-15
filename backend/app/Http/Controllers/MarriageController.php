<?php

namespace App\Http\Controllers;

use App\Models\Marriage;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarriageController extends Controller
{
    /**
     * Link two people as spouses.
     * POST /marriages
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'person_id'     => 'required|exists:people,id',
            'spouse_id'     => 'required|exists:people,id|different:person_id',
            'marriage_date' => 'nullable|date',
        ]);

        // Prevent duplicate marriages for either person
        $alreadyMarriedA = Marriage::where('person_id', $validated['person_id'])
            ->orWhere('spouse_id', $validated['person_id'])
            ->exists();

        $alreadyMarriedB = Marriage::where('person_id', $validated['spouse_id'])
            ->orWhere('spouse_id', $validated['spouse_id'])
            ->exists();

        if ($alreadyMarriedA || $alreadyMarriedB) {
            return response()->json([
                'message' => 'One or both persons are already linked to a spouse.',
            ], 422);
        }

        $marriage = Marriage::create($validated);

        return response()->json([
            'id'            => $marriage->id,
            'person_id'     => $marriage->person_id,
            'spouse_id'     => $marriage->spouse_id,
            'marriage_date' => $marriage->marriage_date?->toDateString(),
        ], 201);
    }

    /**
     * Get marriage record for a given person.
     * GET /marriages/{personId}
     */
    public function getByPerson(string $personId): JsonResponse
    {
        $marriage = Marriage::where('person_id', $personId)
            ->orWhere('spouse_id', $personId)
            ->first();

        if (! $marriage) {
            return response()->json(null);
        }

        return response()->json([
            'id'            => $marriage->id,
            'person_id'     => $marriage->person_id,
            'spouse_id'     => $marriage->spouse_id,
            'marriage_date' => $marriage->marriage_date?->toDateString(),
        ]);
    }

    /**
     * Remove a marriage link.
     * DELETE /marriages/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $marriage = Marriage::findOrFail($id);
        $marriage->delete();

        return response()->json(['message' => 'Marriage record removed.']);
    }
}
