<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
     // Get all events
     public function getAllEvents()
     {
         $events = Event::all();
         return response()->json($events);
     }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         // Fetch all events from the database
         $events = Event::all();

         return response()->json([
             'events' => $events,
         ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       // Validate the request
       $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'date' => 'required|date',
        'location' => 'required|string|max:255',
        'description' => 'required|string',
        'organizer' => 'required|string|max:255',
        'poster' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
    
]);
 // Handle file upload
 if ($request->hasFile('poster')) {
    $posterPath = $request->file('poster')->store('event_posters', 'public'); // Save in storage/app/public/event_posters
} else {
    return response()->json(['message' => 'Poster is required'], 422);
}

// Create the event
$event = Event::create([
    'name' => $validatedData['name'],
    'date' => $validatedData['date'],
    'location' => $validatedData['location'],
    'description' => $validatedData['description'],
    'organizer' => $validatedData['organizer'],
    'poster' => $posterPath,
]);

return response()->json([
    'message' => 'Event added successfully',
    'event' => $event,
], 201);

    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
