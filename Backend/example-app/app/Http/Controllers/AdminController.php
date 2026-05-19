<?php

namespace App\Http\Controllers;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    private function guard(Request $request): void
    {
        if (!$request->user()->is_admin) {
            abort(403, 'Piekļuve liegta');
        }
    }

    public function users(Request $request)
    {
        $this->guard($request);

        $users = User::with('profile')
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'is_admin'   => (bool) $u->is_admin,
                'gamertag'   => $u->profile?->gamertag,
                'platform'   => $u->profile?->platform,
                'region'     => $u->profile?->region,
                'created_at' => $u->created_at?->format('d.m.Y'),
            ]);

        return response()->json($users);
    }

    public function deleteUser(Request $request, int $id)
    {
        $this->guard($request);
        $user = User::findOrFail($id);
        if ($user->id === $request->user()->id) {
            return response()->json(['error' => 'Nevar dzēst pats sevi'], 422);
        }
        $user->delete();
        return response()->json(['ok' => true]);
    }

    public function toggleAdmin(Request $request, int $id)
    {
        $this->guard($request);
        $user = User::findOrFail($id);
        if ($user->id === $request->user()->id) {
            return response()->json(['error' => 'Nevar mainīt savu lomu'], 422);
        }
        $user->is_admin = !$user->is_admin;
        $user->save();
        return response()->json(['is_admin' => (bool) $user->is_admin]);
    }

    public function exportPdf(Request $request)
    {
        $this->guard($request);
        $users = User::with('profile')->get()->map(fn($u) => [
            'name'       => $u->name,
            'email'      => $u->email,
            'is_admin'   => $u->is_admin,
            'gamertag'   => $u->profile?->gamertag ?? '—',
            'platform'   => $u->profile?->platform ?? '—',
            'region'     => $u->profile?->region   ?? '—',
            'created_at' => $u->created_at?->format('d.m.Y'),
        ]);

        $pdf = Pdf::loadView('admin.users_pdf', ['users' => $users])
            ->setPaper('a4', 'landscape');

        return $pdf->download('gamemate_lietotaji_' . now()->format('Ymd') . '.pdf');
    }

    public function exportCsv(Request $request)
    {
        $this->guard($request);
        $users = User::with('profile')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="gamemate_lietotaji_' . now()->format('Ymd') . '.csv"',
        ];

        $callback = function () use ($users) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, ['ID', 'Vārds', 'E-pasts', 'Gamertag', 'Platforma', 'Reģions', 'Loma', 'Reģistrēts']);
            foreach ($users as $u) {
                fputcsv($out, [
                    $u->id,
                    $u->name,
                    $u->email,
                    $u->profile?->gamertag ?? '',
                    $u->profile?->platform ?? '',
                    $u->profile?->region   ?? '',
                    $u->is_admin ? 'Admins' : 'Lietotājs',
                    $u->created_at?->format('d.m.Y'),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function stats(Request $request)
    {
        $this->guard($request);

        $totalUsers    = User::count();
        $totalSwipes   = DB::table('swipes')->count();
        $totalMatches  = DB::table('matches')->count();
        $totalMessages = DB::table('messages')->count();
        $avgMatchesPerUser = $totalUsers > 0
            ? round($totalMatches / $totalUsers, 2)
            : 0;

        $platforms = DB::table('profiles')
            ->select('platform', DB::raw('COUNT(*) as total'))
            ->whereNotNull('platform')->where('platform', '!=', '')
            ->groupBy('platform')->orderByDesc('total')
            ->get();

        $regions = DB::table('profiles')
            ->select('region', DB::raw('COUNT(*) as total'))
            ->whereNotNull('region')->where('region', '!=', '')
            ->groupBy('region')->orderByDesc('total')
            ->get();

        $playstyles = DB::table('profiles')
            ->select('playstyle', DB::raw('COUNT(*) as total'))
            ->whereNotNull('playstyle')->where('playstyle', '!=', '')
            ->groupBy('playstyle')->orderByDesc('total')
            ->get();

        $topMatched = DB::table('matches')
            ->selectRaw('user_a_id as uid, COUNT(*) as cnt')
            ->groupBy('user_a_id')
            ->unionAll(
                DB::table('matches')
                    ->selectRaw('user_b_id as uid, COUNT(*) as cnt')
                    ->groupBy('user_b_id')
            )
            ->orderByDesc('cnt')
            ->limit(5)
            ->get();

        return response()->json([
            'total_users'         => $totalUsers,
            'total_swipes'        => $totalSwipes,
            'total_matches'       => $totalMatches,
            'total_messages'      => $totalMessages,
            'avg_matches_per_user'=> $avgMatchesPerUser,
            'platforms'           => $platforms,
            'regions'             => $regions,
            'playstyles'          => $playstyles,
        ]);
    }
}
