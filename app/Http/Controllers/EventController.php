<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // public function index()
    // {
    //     $events = Event::all(['title', 'start_time', 'end_time'])->toArray();
    //     $eventsJson = json_encode($events);
    //     return view('user.jadwal', compact('eventsJson'));
    // }

    public function index()
    {
        $events = Event::all(['title', 'description', 'start_time', 'end_time']);
        $eventsJson = $events->toJson();
        return view('user.jadwal', compact('events', 'eventsJson'));
    }


    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
        ]);

        Event::create($request->all());

        return redirect()->route('events.index')->with('success', 'Event created successfully.');
    }
}
