<?php

namespace App\Http\Controllers;

use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    /**
     * Display the test result.
     */
    public function show(TestAttempt $testAttempt)
    {
        // Ensure the test attempt belongs to the authenticated user
        if ($testAttempt->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if test is completed
        if ($testAttempt->status !== 'completed') {
            return redirect()->route('test.take', $testAttempt);
        }

        $careerResult = $testAttempt->careerResult;

        if (!$careerResult) {
            abort(404, 'Career result not found');
        }

        return view('test.result', compact('testAttempt', 'careerResult'));
    }

    /**
     * Display user's test history.
     */
    public function history()
    {
        $user = Auth::user();

        $testAttempts = TestAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with(['careerTest', 'careerResult'])
            ->orderBy('completed_at', 'desc')
            ->paginate(10);

        return view('test.history', compact('testAttempts'));
    }
}
