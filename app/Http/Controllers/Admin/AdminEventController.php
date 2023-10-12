<?php

namespace App\Http\Controllers\Admin;

use App\Models\Events\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminEventController extends Controller
{
    //
    public function index()
    {
        $events = Event::all();
        return response()->json($events, 200);
    }

    public function filter(Request $request)
    {
        $status = $request->input('status');
        $recent = $request->input('start_date');

        $filteredEvents = [
            'pendingEvents' => $this->pendingEvents(),
            'activeEvents' => $this->activeEvents(),
            'recentlyCreated' => $this->recentlyCreated(),
        ];

        return response()->json($filteredEvents, 200);
    }


    public function activeEvents()
    {
        $activeEvents = Event::where('status', 'active')->get();
        return response()->json($activeEvents, 200);
    }

    public function pendingEvents()
    {
        $pendingEvents = Event::where('status', 'en attente')->get();
        return response()->json($pendingEvents, 200);
    }

    public function recentlyCreated()
    {
        $recentEvents = Event::orderBy('created_at', 'desc')->take(5)->get();
        return response()->json($recentEvents, 200);
    }
}
