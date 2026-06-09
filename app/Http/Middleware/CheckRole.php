<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('filament.admin.auth.login');
        }

        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }

        return redirect($this->panelUrlForRole($user->role));
    }

    private function panelUrlForRole(string $role): string
    {
        return match ($role) {
            'Admin' => '/admin',
            'Pengawas' => '/supervisory',
            'OperatorSekolah' => '/school',
            default => '/admin',
        };
    }
}
