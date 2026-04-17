<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminAccess = $request->session()->get('admin_access');

        if (!is_array($adminAccess) || empty($adminAccess['user_id']) || empty($adminAccess['granted_at'])) {
            return $this->unauthorizedResponse($request, 'Silakan login admin terlebih dahulu.');
        }

        if ((now()->getTimestamp() - (int) $adminAccess['granted_at']) > 60 * 60 * 8) {
            $request->session()->forget('admin_access');

            return $this->unauthorizedResponse($request, 'Sesi admin sudah berakhir. Silakan login ulang.');
        }

        $isValidAdmin = User::query()
            ->whereKey((int) $adminAccess['user_id'])
            ->where('role', 'admin')
            ->where('is_active', true)
            ->exists();

        if (!$isValidAdmin) {
            $request->session()->forget('admin_access');

            return $this->unauthorizedResponse($request, 'Akses admin tidak valid.');
        }

        return $next($request);
    }

    private function unauthorizedResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'unauthorized',
                'message' => $message,
            ], 401);
        }

        return redirect()->route('dashboard.public')->with('error', $message);
    }
}
