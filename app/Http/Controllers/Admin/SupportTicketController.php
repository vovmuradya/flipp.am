<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function (Request $request, $next) {
            if (!Auth::user()->hasPermission('view_support') && !Auth::user()->isAdmin()) {
                abort(403, 'У вас нет доступа к службе поддержки');
            }
            return $next($request);
        });
    }

    /**
     * Отображает список тикетов поддержки
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignedUser'])->orderBy('created_at', 'desc');

        // Фильтрация по статусу
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Фильтрация по приоритету
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        // Фильтрация по категории
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Фильтрация по пользователю
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        $tickets = $query->paginate(20);
        $users = User::all(); // Для фильтрации по пользователям

        return view('admin.support-tickets.index', compact('tickets', 'users'));
    }

    /**
     * Отображает форму создания нового тикета
     */
    public function create()
    {
        $users = User::all();
        return view('admin.support-tickets.create', compact('users'));
    }

    /**
     * Сохраняет новый тикет
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical',
            'category' => 'required|in:technical,billing,account,listing,verification,other',
        ]);

        SupportTicket::create($request->only([
            'user_id', 'subject', 'description', 'priority', 'category'
        ]));

        return redirect()->route('admin.support-tickets.index')->with('success', 'Тикет успешно создан');
    }

    /**
     * Отображает тикет
     */
    public function show($id)
    {
        $ticket = SupportTicket::with(['user', 'assignedUser'])->findOrFail($id);
        return view('admin.support-tickets.show', compact('ticket'));
    }

    /**
     * Отображает форму редактирования тикета
     */
    public function edit($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $users = User::all(); // Для назначения агента поддержки
        return view('admin.support-tickets.edit', compact('ticket', 'users'));
    }

    /**
     * Обновляет тикет
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:open,in_progress,resolved,closed,waiting',
            'resolution_notes' => 'nullable|string',
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $ticket->update($request->only(['assigned_to', 'status', 'resolution_notes']));

        // Обновляем время решения, если статус изменился на "resolved"
        if ($request->status === 'resolved' && !$ticket->resolved_at) {
            $ticket->update(['resolved_at' => now()]);
        }

        // Обновляем время закрытия, если статус изменился на "closed"
        if ($request->status === 'closed' && !$ticket->closed_at) {
            $ticket->update(['closed_at' => now()]);
        }

        return redirect()->route('admin.support-tickets.index')->with('success', 'Тикет успешно обновлен');
    }

    /**
     * Назначает тикет агенту поддержки
     */
    public function assign(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $ticket->update(['assigned_to' => $request->assigned_to]);

        return redirect()->back()->with('success', 'Тикет успешно назначен');
    }

    /**
     * Закрывает тикет
     */
    public function close($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->update([
            'status' => 'closed',
            'closed_at' => now()
        ]);

        return redirect()->back()->with('success', 'Тикет успешно закрыт');
    }
}
