<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CareerTest;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected AuditLogger $audit)
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $totalTests = CareerTest::count();
        $totalQuestions = Question::count();
        $totalAttempts = TestAttempt::count();
        $completedAttempts = TestAttempt::where('status', 'completed')->count();

        $recentTests = CareerTest::withCount('testAttempts')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('totalTests', 'totalQuestions', 'totalAttempts', 'completedAttempts', 'recentTests'));
    }

    public function auditLogs()
    {
        $logs = AuditLog::with('user')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('admin.audit-logs', compact('logs'));
    }

    /**
     * Display a listing of career tests.
     */
    public function tests()
    {
        $tests = CareerTest::withCount(['questions', 'testAttempts'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.tests.index', compact('tests'));
    }

    /**
     * Show the form for creating a new career test.
     */
    public function createTest()
    {
        return view('admin.tests.create');
    }

    /**
     * Store a newly created career test.
     */
    public function storeTest(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration_minutes' => 'required|integer|min:1|max:300',
        ]);

        $test = CareerTest::create($request->all());

        $this->audit->log('admin.test.created', Auth::id(), 'CareerTest', $test->id, $test->title);

        return redirect()->route('admin.tests')->with('success', 'Career test created successfully.');
    }

    /**
     * Show the form for editing a career test.
     */
    public function editTest(CareerTest $careerTest)
    {
        $careerTest->load('questions');
        return view('admin.tests.edit', compact('careerTest'));
    }

    /**
     * Update the specified career test.
     */
    public function updateTest(Request $request, CareerTest $careerTest)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'is_active' => 'boolean',
        ]);

        $careerTest->update($request->all());

        $this->audit->log('admin.test.updated', Auth::id(), 'CareerTest', $careerTest->id, $careerTest->title);

        return redirect()->route('admin.tests')->with('success', 'Career test updated successfully.');
    }

    /**
     * Remove the specified career test.
     */
    public function destroyTest(CareerTest $careerTest)
    {
        $this->audit->log('admin.test.deleted', Auth::id(), 'CareerTest', $careerTest->id, $careerTest->title);

        $careerTest->delete();

        return redirect()->route('admin.tests')->with('success', 'Career test deleted successfully.');
    }

    /**
     * Show questions for a career test.
     */
    public function questions(CareerTest $careerTest)
    {
        $questions = $careerTest->questions()->orderBy('order')->get();
        return view('admin.tests.questions', compact('careerTest', 'questions'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function createQuestion(CareerTest $careerTest)
    {
        return view('admin.questions.create', compact('careerTest'));
    }

    /**
     * Store a newly created question.
     */
    public function storeQuestion(Request $request, CareerTest $careerTest)
    {
        $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,scale,short_answer',
            'options' => 'required_if:question_type,multiple_choice|array',
            'options.*' => 'string',
            'order' => 'required|integer|min:0',
        ]);

        $questionData = $request->all();
        $questionData['career_test_id'] = $careerTest->id;

        if ($request->question_type === 'multiple_choice' && $request->options) {
            $questionData['options'] = $request->options;
        }

        $question = Question::create($questionData);

        $this->audit->log('admin.question.created', Auth::id(), 'Question', $question->id, $question->question_text);

        return redirect()->route('admin.tests.questions', $careerTest)->with('success', 'Question created successfully.');
    }

    /**
     * Show the form for editing a question.
     */
    public function editQuestion(Question $question)
    {
        return view('admin.questions.edit', compact('question'));
    }

    /**
     * Update the specified question.
     */
    public function updateQuestion(Request $request, Question $question)
    {
        $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,scale,short_answer',
            'options' => 'required_if:question_type,multiple_choice|array',
            'options.*' => 'string',
            'order' => 'required|integer|min:0',
        ]);

        $questionData = $request->all();

        if ($request->question_type === 'multiple_choice' && $request->options) {
            $questionData['options'] = $request->options;
        } else {
            $questionData['options'] = null;
        }

        $question->update($questionData);

        $this->audit->log('admin.question.updated', Auth::id(), 'Question', $question->id, $question->question_text);

        return redirect()->route('admin.tests.questions', $question->careerTest)->with('success', 'Question updated successfully.');
    }

    /**
     * Remove the specified question.
     */
    public function destroyQuestion(Question $question)
    {
        $careerTest = $question->careerTest;

        $this->audit->log('admin.question.deleted', Auth::id(), 'Question', $question->id, $question->question_text);

        $question->delete();

        return redirect()->route('admin.tests.questions', $careerTest)->with('success', 'Question deleted successfully.');
    }
}
