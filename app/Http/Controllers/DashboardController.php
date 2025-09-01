<?php

namespace App\Http\Controllers;

use App\Models\CareerTest;
use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the student dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Get available career tests
        $availableTests = CareerTest::where('is_active', true)->get();

        // Get user's test attempts
        $testAttempts = TestAttempt::where('user_id', $user->id)
            ->with(['careerTest', 'careerResult'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard', compact('availableTests', 'testAttempts'));
    }
}
