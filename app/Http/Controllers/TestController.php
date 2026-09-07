<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    /**
     * GET /api/test/ping
     * Pure PHP response — no database, no logic.
     */
    public function ping()
    {
        return response()->json([
            'pong' => true,
            'timestamp' => microtime(true),
            'message' => 'Endpoint sederhana tanpa database'
        ]);
    }

    /**
     * GET /api/test/plain
     * Minimal response — plain text, no JSON overhead.
     */
    public function plain()
    {
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * GET /api/test/db-ping
     * Simple database query — SELECT 1.
     */
    public function dbPing()
    {
        $start = microtime(true);
        $result = DB::select('SELECT 1 as check');
        $duration = (microtime(true) - $start) * 1000;

        return response()->json([
            'success' => true,
            'query' => 'SELECT 1',
            'result' => $result,
            'duration_ms' => round($duration, 2),
            'message' => 'Query sederhana ke database'
        ]);
    }

    /**
     * GET /api/test/muzakki-count
     * Count muzakki — mirror of real query but minimal.
     */
    public function countMuzakki()
    {
        $start = microtime(true);
        $count = DB::table('muzakki')->where('tipe_muzakki', 'terdaftar')->count();
        $duration = (microtime(true) - $start) * 1000;

        return response()->json([
            'count' => $count,
            'duration_ms' => round($duration, 2),
            'message' => 'Count muzakki terdaftar'
        ]);
    }
}