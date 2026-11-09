<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsAppLink;
use App\Models\Complaint;
use App\Models\Event;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $eventId = $request->query('event_id');
        $selectedEvent = null;

        if ($eventId !== null) {
            $eventId = filter_var($eventId, FILTER_VALIDATE_INT);
            abort_unless($eventId !== false && $eventId > 0, 404);
            $selectedEvent = Event::with('room')->findOrFail($eventId);
        }

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in([
                'all',
                Complaint::STATUS_PENDING,
                Complaint::STATUS_PARTS,
                Complaint::STATUS_DONE,
            ])],
        ]);
        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? Complaint::STATUS_PENDING;

        $rooms = Room::query()->orderBy('room_name')->get();
        $complaints = Complaint::with(['room', 'reporter', 'handler', 'event'])
            ->when($status !== 'all', fn ($query) => $query->where('status_repair', $status))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('description', 'like', '%'.$search.'%')
                        ->orWhere('status_repair', 'like', '%'.$search.'%')
                        ->orWhereHas('room', fn ($roomQuery) => $roomQuery->where('room_name', 'like', '%'.$search.'%'))
                        ->orWhereHas('reporter', fn ($reporterQuery) => $reporterQuery->where('fullname', 'like', '%'.$search.'%'))
                        ->orWhereHas('event', fn ($eventQuery) => $eventQuery->where('event_name', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('complaints.index', compact('complaints', 'rooms', 'selectedEvent', 'search', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'event_id' => [
                'nullable',
                'integer',
                Rule::exists('events', 'id')->where('room_id', $request->input('room_id')),
            ],
            'description' => ['required', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = null;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('complaints', 'public');

            if ($path === false) {
                return back()->withErrors(['photo' => 'Foto bukti gagal disimpan. Silakan coba kembali.'])->withInput();
            }
        }

        $complaint = Complaint::create([
            'room_id' => $validated['room_id'],
            'event_id' => $validated['event_id'] ?? null,
            'reporter_id' => $request->user()->id,
            'description' => $validated['description'],
            'photo_path' => $path,
            'status_repair' => Complaint::STATUS_PENDING,
            'reported_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Pengaduan berhasil dikirim.');
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        $user = $request->user();

        abort_unless(
            $user->isAdmin() || ($user->isPic() && $complaint->room && $complaint->room->pic_id === $user->id),
            403,
            'Anda tidak berhak mengubah status pengaduan ini.'
        );

        $validated = $request->validate([
            'status_repair' => ['required', 'in:'.implode(',', [Complaint::STATUS_PENDING, Complaint::STATUS_PARTS, Complaint::STATUS_DONE])],
        ]);

        $complaint->status_repair = $validated['status_repair'];
        $complaint->handler_id = $user->id;
        $complaint->save();

        return back()->with('success', 'Status perbaikan berhasil diperbarui.');
    }

    public function escalate(Complaint $complaint)
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Hanya Waka yang dapat mengirim peringatan.');

        $link = WhatsAppLink::forComplaint($complaint);

        return redirect()->away($link);
    }
}
