<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Conversation;
use App\Models\Message;
use App\Support\MessagingHours;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * RF-27 / RF-62: la familia escribe al director de grupo de su hijo.
 */
class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $children = $user->children()->with('classroom.director')->orderBy('first_name')->get();
        // Mensajes del colegio sin leer, por hijo.
        $unread = Message::query()
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->where('conversations.guardian_id', $user->id)
            ->where('messages.sender_id', '!=', $user->id)
            ->whereNull('messages.read_at')
            ->selectRaw('conversations.child_id, count(*) as total')
            ->groupBy('conversations.child_id')
            ->pluck('total', 'child_id');

        return view('guardian.messages.index', compact('children', 'unread'));
    }

    public function show(Request $request, Child $child): View
    {
        Gate::authorize('view', $child);
        $conversation = $this->conversation($request, $child);

        // Al abrirla, los mensajes del colegio quedan leídos.
        $conversation->messages()->where('sender_id', '!=', $request->user()->id)->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('guardian.messages.show', [
            'child' => $child->load('classroom.director'),
            'conversation' => $conversation,
            'messages' => $conversation->messages()->with('sender')->oldest()->get(),
            'isOpen' => MessagingHours::isOpen(),
        ]);
    }

    public function store(Request $request, Child $child): RedirectResponse
    {
        Gate::authorize('view', $child);

        $data = $request->validate(
            ['body' => ['required', 'string', 'max:1000']],
            ['body.required' => 'Escribe tu mensaje.', 'body.max' => 'Es muy largo. Máximo 1000 caracteres.']
        );

        $conversation = $this->conversation($request, $child);
        $deliveredAt = MessagingHours::deliveryTime();

        $message = new Message(['body' => $data['body']]);
        $message->forceFill([
            'conversation_id' => $conversation->id,
            'sender_id' => $request->user()->id,
            'visible_at' => $deliveredAt, // fuera de horario: el docente lo ve a las 6:00 a. m.
        ])->save();
        $conversation->forceFill(['last_message_at' => now()])->save();

        $status = $deliveredAt->isFuture()
            ? 'Listo, tu mensaje quedó enviado. Le llegará al docente a las '.MessagingHours::opensAtLabel().'.'
            : 'Listo, tu mensaje quedó enviado.';

        return redirect()->route('guardian.messages.show', $child)->with('status', $status);
    }

    // Una conversación por niño y acudiente; se crea la primera vez.
    private function conversation(Request $request, Child $child): Conversation
    {
        return Conversation::firstOrCreate(['child_id' => $child->id, 'guardian_id' => $request->user()->id]);
    }
}
