<?php

namespace App\Http\Controllers;

use App\Models\Marriage;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PersonController extends Controller
{
    public function index(): JsonResponse
    {
        $people = Person::where('user_id', auth()->id())->get();
        return response()->json($people);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'sometimes',
            'gender'      => 'required|in:male,female',
            'birth_date'  => 'nullable|date',
            'death_date'  => 'nullable|date',
            'parent_id'   => 'nullable|exists:people,id',
            'photo'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $validated['user_id'] = auth()->id();
        $person = Person::create($validated);
        return response()->json($person, 201);
    }

    public function show(string $id): JsonResponse
    {
        $person = Person::with(['parent', 'children'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $data           = $person->toArray();
        $data['spouse'] = $this->getSpouseFor($person->id);

        return response()->json($data);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $person = Person::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'sometimes',
            'gender'      => 'sometimes|in:male,female',
            'birth_date'  => 'nullable|date',
            'death_date'  => 'nullable|date',
            'parent_id'   => 'nullable|exists:people,id',
            'photo'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($person->photo) {
                Storage::disk('public')->delete($person->photo);
            }
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $person->update($validated);
        return response()->json($person);
    }

    public function destroy(string $id): JsonResponse
    {
        $person = Person::where('user_id', auth()->id())->findOrFail($id);
        if ($person->photo) {
            Storage::disk('public')->delete($person->photo);
        }
        $person->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function tree(string $id): JsonResponse
    {
        $person = Person::where('user_id', auth()->id())->findOrFail($id);
        return response()->json($this->buildTree($person));
    }

    public function roots(): JsonResponse
    {
        $roots = Person::where('user_id', auth()->id())
            ->whereNull('parent_id')
            ->get();

        $trees = $roots->map(fn($root) => $this->buildTree($root));
        return response()->json($trees);
    }

    public function publicTree(string $token): JsonResponse
    {
        $user = User::where('share_token', $token)->first();

        if (! $user) {
            return response()->json(['message' => 'Share link not found'], 404);
        }

        $roots = Person::where('user_id', $user->id)
            ->whereNull('parent_id')
            ->get();

        $trees = $roots->map(fn($root) => $this->buildTree($root));

        return response()->json([
            'owner' => $user->name,
            'trees' => $trees,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function buildTree(Person $person): array
    {
        $data           = $person->toArray();
        $data['spouse'] = $this->getSpouseFor($person->id);
        $data['children'] = $person->children()->get()
            ->map(fn($child) => $this->buildTree($child))
            ->toArray();

        return $data;
    }

    /**
     * Look up the spouse of a person and return a compact summary array,
     * or null if the person is not married.
     */
    private function getSpouseFor(int $personId): ?array
    {
        $marriage = Marriage::where('person_id', $personId)
            ->orWhere('spouse_id', $personId)
            ->first();

        if (! $marriage) {
            return null;
        }

        $spouseId = $marriage->person_id == $personId
            ? $marriage->spouse_id
            : $marriage->person_id;

        $spouse = Person::find($spouseId);
        if (! $spouse) {
            return null;
        }

        return [
            'id'            => $spouse->id,
            'name'          => $spouse->name,
            'gender'        => $spouse->gender,
            'photo_url'     => $spouse->photo_url,
            'birth_date'    => $spouse->birth_date?->toDateString(),
            'death_date'    => $spouse->death_date?->toDateString(),
            'marriage_date' => $marriage->marriage_date?->toDateString(),
            'marriage_id'   => $marriage->id,
        ];
    }
}
