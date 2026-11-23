<?php

namespace App\Http\Controllers\Educator;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * RF-27 / RF-62: bandeja del director de grupo (y de coordinación).
 * Los mensajes enviados fuera de horario aparecen a partir de las 6:00 a. m.
 */
class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->when($user->role === Role::Educator, fn ($q) => $q->whereHas(
                'child', fn ($c) => $c->whereIn('classroom_id', $user->ledClassrooms()->pluck('classrooms.id'))
            ))
            // Solo conversaciones con al menos un mensaje ya visible para el colegio.
            ->whereHas('messages', fn ($m) => $m->visible())
            ->withCount(['messages as unread_count' => fn ($m) => $m->visible()
                ->whereColumn('messages.sender_id', 'conversations.guardian_id')->whereNull('read_at')])
            ->with(['child', 'guardian'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('educator.messages.index', compact('conversations'));
    }

    public function show(Request $request, Conversation $conversation): View
    {
        Gate::authorize('view', $conversation);

        // Marcar como leídos los mensajes de la familia que ya están en horario.
        $conversation->messages()->visible()
            ->where('sender_id', $conversation->guardian_id)->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('educator.messages.show', [
            'conversation' => $conversation->load('child', 'guardian'),
            'messages' => $conversation->messages()->visible()->with('sender')->oldest()->get(),
        ]);
    }

    public function store(Request $request, Conversation $conversation): RedirectResponse
    {
        Gate::authorize('view', $conversation);

        $data = $request->validate(
            ['body' => ['required', 'string', 'max:1000']],
            ['body.required' => 'Escribe tu respuesta.', 'body.max' => 'Es muy largo. Máximo 1000 caracteres.']
        );

        // La respuesta del colegio le llega a la familia de inmediato.
        $message = new Message(['body' => $data['body']]);
        $message->forceFill([
            'conversation_id' => $conversation->id,
            'sender_id' => $request->user()->id,
            'visible_at' => now(),
        ])->save();
        $conversation->forceFill(['last_message_at' => now()])->save();

        return redirect()->route('educator.messages.show', $conversation)->with('status', 'Listo, tu respuesta quedó enviada.');
    }
}
