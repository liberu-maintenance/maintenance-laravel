<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\GuestWorkOrderSubmitted;

class GuestWorkOrderController extends Controller
{
    /**
     * Show the guest work order submission form
     */
    public function create()
    {
        return view('guest.work-order-form');
    }

    /**
     * Store a guest work order submission
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'priority' => 'required|in:low,medium,high,critical',
            'location' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'equipment_details' => 'nullable|string|max:500',
            'preferred_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors below.');
        }

        try {
            // Create work order with guest submission data
            $workOrder = WorkOrder::create([
                'title' => $request->title,
                'description' => $request->description,
                'priority' => $request->priority,
                'status' => 'pending',
                'location' => $request->location,
                'contact_name' => $request->contact_name,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'equipment_details' => $request->equipment_details,
                'preferred_date' => $request->preferred_date,
                'submitted_by_guest' => true,
                'company_id' => 1, // Default company for guest submissions
            ]);

            // Send notification email to admin (optional)
            // Mail::to(config('mail.admin_email'))->send(new GuestWorkOrderSubmitted($workOrder));

            return redirect()->route('guest.work-order.success')
                ->with('success', 'Your maintenance request has been submitted successfully! We will contact you soon.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'There was an error submitting your request. Please try again.');
        }
    }

    /**
     * Show success page after submission
     */
    public function success()
    {
        return view('guest.work-order-success');
    }
}