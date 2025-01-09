<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = Alert::with('inventory')
            ->latest('created_at')
            ->get();
        
        return view('alerts.index', compact('alerts'));
    }

    public function markAsRead(Alert $alert)
    {
        $alert->markAsRead();
        return back()->with('success', 'Alert marked as read');
    }
} 