<?php

namespace App\Http\Controllers;

use App\Models\CareerTest;
use App\Models\TestAttempt;
use App\Models\Answer;
use App\Services\AuditLogger;
use App\Services\CareerMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function __construct(
        protected CareerMatchingService $matchingService,
        protected AuditLogger $audit,
    ) {}

    /**
     * Start a career test.
     */
    public function start(CareerTest $careerTest)
    {
        $userId = Auth::id();

        $existingAttempt = TestAttempt::where('user_id', $userId)
            ->where('career_test_id', $careerTest->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            return redirect()->route('test.take', $existingAttempt);
        }

        $testAttempt = TestAttempt::create([
            'user_id'        => $userId,
            'career_test_id' => $careerTest->id,
            'started_at'     => now(),
            'status'         => 'in_progress',
        ]);

        return redirect()->route('test.take', $testAttempt);
    }

    /**
     * Display the current test question.
     */
    public function take(TestAttempt $testAttempt)
    {
        if ($testAttempt->user_id !== Auth::id()) {
            abort(403);
        }

        if ($testAttempt->status === 'completed') {
            return redirect()->route('test.result', $testAttempt);
        }

        $questions            = $testAttempt->careerTest->questions;
        $currentQuestionIndex = $testAttempt->answers()->count();

        if ($currentQuestionIndex >= $questions->count()) {
            return $this->completeTest($testAttempt);
        }

        $currentQuestion = $questions[$currentQuestionIndex];

        return view('test.take', compact('testAttempt', 'currentQuestion', 'currentQuestionIndex', 'questions'));
    }

    /**
     * Submit an answer for a question.
     */
    public function submitAnswer(Request $request, TestAttempt $testAttempt)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer_text' => 'required|string',
            'score'       => 'nullable|integer|min:1|max:10',
        ]);

        if ($testAttempt->user_id !== Auth::id()) {
            abort(403);
        }

        if ($testAttempt->status === 'completed') {
            return redirect()->route('test.result', $testAttempt);
        }

        // Score is only submitted from the form for scale questions;
        // multiple_choice and short_answer scores are calculated by the service at completion.
        Answer::create([
            'test_attempt_id' => $testAttempt->id,
            'question_id'     => $request->question_id,
            'answer_text'     => $request->answer_text,
            'score'           => $request->score, // null for mc/short_answer — filled later
        ]);

        return redirect()->route('test.take', $testAttempt);
    }

    /**
     * Complete the test and generate results.
     */
    private function completeTest(TestAttempt $testAttempt)
    {
        DB::transaction(function () use ($testAttempt) {
            $testAttempt->update([
                'completed_at' => now(),
                'status'       => 'completed',
            ]);

            $this->matchingService->generate($testAttempt);
        });

        $this->audit->log(
            'test.completed',
            Auth::id(),
            'CareerTest',
            $testAttempt->career_test_id,
            $testAttempt->careerTest->title,
        );

        return redirect()->route('test.result', $testAttempt);
    }
}
