<?php

namespace App\Http\Controllers;

use App\Models\Marriage;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelationshipController extends Controller
{
    /**
     * Detect the relationship between two people.
     * POST /relationships/check
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'person_a_id' => 'required|exists:people,id',
            'person_b_id' => 'required|exists:people,id',
        ]);

        $personA = Person::findOrFail($request->person_a_id);
        $personB = Person::findOrFail($request->person_b_id);

        $result = $this->detectRelationship($personA, $personB);

        return response()->json([
            'person_a' => [
                'id'     => $personA->id,
                'name'   => $personA->name,
                'gender' => $personA->gender,
            ],
            'person_b' => [
                'id'     => $personB->id,
                'name'   => $personB->name,
                'gender' => $personB->gender,
            ],
            'found'   => $result['found'],
            'type'    => $result['type'],
            'a_to_b'  => $result['a_to_b'],
            'b_to_a'  => $result['b_to_a'],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Core relationship detection logic
    // ──────────────────────────────────────────────────────────────────────────

    private function detectRelationship(Person $a, Person $b): array
    {
        if ($a->id === $b->id) {
            return $this->result(false, 'same_person', null, null);
        }

        // 1. Spouse ──────────────────────────────────────────────────────────
        $marriage = Marriage::where(function ($q) use ($a, $b) {
            $q->where('person_id', $a->id)->where('spouse_id', $b->id);
        })->orWhere(function ($q) use ($a, $b) {
            $q->where('person_id', $b->id)->where('spouse_id', $a->id);
        })->first();

        if ($marriage) {
            return $this->result(
                true, 'spouse',
                $a->gender === 'male' ? 'Husband' : 'Wife',
                $b->gender === 'male' ? 'Husband' : 'Wife',
            );
        }

        // 2. Parent → Child (A is parent of B) ──────────────────────────────
        if ($b->parent_id === $a->id) {
            return $this->result(
                true, 'parent_child',
                $a->gender === 'male' ? 'Father'   : 'Mother',
                $b->gender === 'male' ? 'Son'       : 'Daughter',
            );
        }

        // 3. Parent → Child (B is parent of A) ──────────────────────────────
        if ($a->parent_id === $b->id) {
            return $this->result(
                true, 'parent_child',
                $a->gender === 'male' ? 'Son'       : 'Daughter',
                $b->gender === 'male' ? 'Father'   : 'Mother',
            );
        }

        // 4. Siblings (same parent) ──────────────────────────────────────────
        if ($a->parent_id && $a->parent_id === $b->parent_id) {
            return $this->result(
                true, 'siblings',
                $a->gender === 'male' ? 'Brother' : 'Sister',
                $b->gender === 'male' ? 'Brother' : 'Sister',
            );
        }

        // 5. Grandparent (A is grandparent of B) ────────────────────────────
        if ($b->parent_id) {
            $parentB = Person::find($b->parent_id);
            if ($parentB && $parentB->parent_id === $a->id) {
                return $this->result(
                    true, 'grandparent',
                    $a->gender === 'male' ? 'Grandfather'   : 'Grandmother',
                    $b->gender === 'male' ? 'Grandson'      : 'Granddaughter',
                );
            }
        }

        // 6. Grandparent (B is grandparent of A) ────────────────────────────
        if ($a->parent_id) {
            $parentA = Person::find($a->parent_id);
            if ($parentA && $parentA->parent_id === $b->id) {
                return $this->result(
                    true, 'grandparent',
                    $a->gender === 'male' ? 'Grandson'      : 'Granddaughter',
                    $b->gender === 'male' ? 'Grandfather'   : 'Grandmother',
                );
            }
        }

        // 7. Uncle / Aunt (B is sibling of A's parent → B is uncle/aunt of A)
        if ($a->parent_id) {
            $parentA = Person::find($a->parent_id);
            if ($parentA && $parentA->parent_id && $b->parent_id === $parentA->parent_id) {
                return $this->result(
                    true, 'uncle_aunt',
                    $a->gender === 'male' ? 'Nephew' : 'Niece',
                    $b->gender === 'male' ? 'Uncle'  : 'Aunt',
                );
            }
        }

        // 8. Uncle / Aunt (A is sibling of B's parent → A is uncle/aunt of B)
        if ($b->parent_id) {
            $parentB = Person::find($b->parent_id);
            if ($parentB && $parentB->parent_id && $a->parent_id === $parentB->parent_id) {
                return $this->result(
                    true, 'uncle_aunt',
                    $a->gender === 'male' ? 'Uncle'  : 'Aunt',
                    $b->gender === 'male' ? 'Nephew' : 'Niece',
                );
            }
        }

        // 9. Cousins (parents are siblings) ─────────────────────────────────
        if ($a->parent_id && $b->parent_id) {
            $parentA = Person::find($a->parent_id);
            $parentB = Person::find($b->parent_id);
            if (
                $parentA && $parentB
                && $parentA->parent_id
                && $parentB->parent_id
                && $parentA->parent_id === $parentB->parent_id
            ) {
                return $this->result(true, 'cousins', 'Cousin', 'Cousin');
            }
        }

        // Not found ──────────────────────────────────────────────────────────
        return $this->result(false, 'unknown', null, null);
    }

    private function result(bool $found, string $type, ?string $aToB, ?string $bToA): array
    {
        return compact('found', 'type', 'aToB', 'bToA') + ['a_to_b' => $aToB, 'b_to_a' => $bToA];
    }
}
