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
        $user = $request->user();

        $rooms = Room::query()->orderBy('room_name')->get();
        $complaints = Complaint::with(['room', 'reporter', 'handler', 'event'])
            ->when(! $user->isAdmin() && ! $user->isPic(), function ($query) use ($user): void {
                // Guru, Organisasi (OSIS), Siswa, dan pelapor lainnya hanya melihat laporan yang mereka buat sendiri
                $query->where('reporter_id', $user->id);
            })
            ->when($user->isPic(), function ($query) use ($user): void {
                // PIC Ruang hanya melihat pengaduan yang disampaikan terkait ruang yang mereka kelola atau ditangani oleh mereka
                $query->where(function ($q) use ($user): void {
                    $q->whereHas('room', fn ($roomQuery) => $roomQuery->where('pic_id', $user->id))
                        ->orWhere('handler_id', $user->id);
                });
            })
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
        if ($request->user()->isPic()) {
            return back()->withErrors(['error' => 'PIC Ruang tidak dapat membuat pengaduan baru, hanya menangani pengaduan yang disampaikan.']);
        }

        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'event_id' => [
                'nullable',
                'integer',
                Rule::exists('events', 'id')->where('room_id', $request->input('room_id')),
            ],
            'description' => ['required', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,txt,zip,rar,csv', 'max:20480'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('complaints', 'public');

            if ($photoPath === false) {
                return back()->withErrors(['photo' => 'Foto bukti gagal disimpan. Silakan coba kembali.'])->withInput();
            }
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('complaints/files', 'public');

            if ($filePath === false) {
                return back()->withErrors(['file' => 'Berkas dokumen gagal disimpan. Silakan coba kembali.'])->withInput();
            }
        }

        $complaint = Complaint::create([
            'room_id' => $validated['room_id'],
            'event_id' => $validated['event_id'] ?? null,
            'reporter_id' => $request->user()->id,
            'description' => $validated['description'],
            'photo_path' => $photoPath,
            'file_path' => $filePath,
            'status_repair' => Complaint::STATUS_PENDING,
            'reported_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Pengaduan berhasil dikirim.');
    }

    public function updateStatus(Request $request, Complaint $complaint): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $user->isAdmin() || ($user->isPic() && $complaint->room && $complaint->room->pic_id === $user->id),
            403,
            'Anda tidak berhak mengubah status pengaduan ini.'
        );

        $validated = $request->validate([
            'status_repair' => ['required', 'in:'.implode(',', [Complaint::STATUS_PENDING, Complaint::STATUS_PARTS, Complaint::STATUS_DONE])],
            'handler_note' => ['nullable', 'string', 'max:1000'],
            'handler_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'handler_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,txt,zip,rar,csv', 'max:20480'],
        ]);

        if ($request->hasFile('handler_photo')) {
            $complaint->handler_photo_path = $request->file('handler_photo')->store('complaints/handlers', 'public');
        }

        if ($request->hasFile('handler_file')) {
            $complaint->handler_file_path = $request->file('handler_file')->store('complaints/handlers', 'public');
        }

        if (array_key_exists('handler_note', $validated)) {
            $complaint->handler_note = $validated['handler_note'];
        }

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
