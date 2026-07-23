<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public const ROLES = [
        'administrador' => 'Administrador',
        'redactor' => 'Redactor',
        'invitado' => 'Invitado',
    ];

    public string $buscar = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('administrar'), 403);
    }

    public function cambiarRol(int $id, string $rol): void
    {
        if (! array_key_exists($rol, self::ROLES)) {
            return;
        }

        if ($id === auth()->id()) {
            return;
        }

        User::findOrFail($id)->update(['rol' => $rol]);
    }

    public function with(): array
    {
        return [
            'usuarios' => User::query()
                ->with('pueblo')
                ->when($this->buscar, fn ($q) => $q->where('name', 'like', "%{$this->buscar}%")
                    ->orWhere('email', 'like', "%{$this->buscar}%"))
                ->orderBy('name')
                ->paginate(15),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Usuarios</h2>
    </div>

    <div class="mb-4">
        <x-text-input wire:model.live.debounce.300ms="buscar" type="text" class="w-full" placeholder="Buscar por nombre o email..." />
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Pueblo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Rol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($usuarios as $usuario)
                        <tr wire:key="usuario-{{ $usuario->id }}">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $usuario->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $usuario->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $usuario->pueblo?->nombre ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if ($usuario->id === auth()->id())
                                    <span class="text-gray-500 dark:text-gray-400">{{ self::ROLES[$usuario->rol] }} (tú)</span>
                                @else
                                    <select
                                        wire:change="cambiarRol({{ $usuario->id }}, $event.target.value)"
                                        class="border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                    >
                                        @foreach (self::ROLES as $valor => $etiqueta)
                                            <option value="{{ $valor }}" @selected($usuario->rol === $valor)>{{ $etiqueta }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4">
            {{ $usuarios->links() }}
        </div>
    </div>
</div>
