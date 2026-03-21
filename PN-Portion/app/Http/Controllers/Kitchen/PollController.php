<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\MenuItem;
use Carbon\Carbon;

class PollController extends Controller
{
    /**
     * Display a listing of the meal polls.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $polls = Poll::orderBy('poll_date', 'desc')->paginate(10);
        return view('kitchen.polls.index', compact('polls'));
    }

    /**
     * Show the form for creating a new poll.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuItems = MenuItem::all();
        return view('kitchen.polls.create', compact('menuItems'));
    }

    /**
     * Store a newly created poll in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'poll_date' => 'required|date',
            'meal_type' => 'required|in:breakfast,lunch,dinner',
            'menu_items' => 'required|array',
            'menu_items.*' => 'exists:menu_items,id',
            'instructions' => 'nullable|string',
        ]);

        $poll = new Poll();
        $poll->poll_date = $request->poll_date;
        $poll->meal_type = $request->meal_type;
        $poll->instructions = $request->instructions;
        $poll->created_by = auth()->user()->user_id;
        $poll->save();
        
        // Attach menu items to the poll
        $poll->menuItems()->attach($request->menu_items);

        return redirect()->route('kitchen.pre-orders')
            ->with('success', 'Meal poll created successfully!');
    }

    /**
     * Display the specified poll.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $poll = Poll::findOrFail($id);
        return view('kitchen.polls.show', compact('poll'));
    }

    /**
     * Remove the specified poll from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $poll = Poll::findOrFail($id);
        $poll->delete();

        return redirect()->route('kitchen.polls.index')
            ->with('success', 'Poll deleted successfully');
    }
}
