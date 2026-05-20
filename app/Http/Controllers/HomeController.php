<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Store a guest work order submission.
     */
    public function submitWorkOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'priority' => 'required|in:low,medium,high,urgent',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'equipment' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $workOrder = WorkOrder::create([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'guest_name' => $request->guest_name,
            'guest_email' => $request->guest_email,
            'guest_phone' => $request->guest_phone,
            'location' => $request->location,
            'equipment' => $request->equipment,
            'submitted_at' => now(),
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Your work order has been submitted successfully! We will review it and get back to you soon.');
    }
}