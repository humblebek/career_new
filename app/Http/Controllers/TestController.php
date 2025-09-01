<?php

namespace App\Http\Controllers;

use App\Models\CareerTest;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\Answer;
use App\Models\CareerResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    /**
     * Start a career test.
     */
    public function start(CareerTest $careerTest)
    {
        $user = Auth::user();

        // Check if user already has an active attempt for this test
        $existingAttempt = TestAttempt::where('user_id', $user->id)
            ->where('career_test_id', $careerTest->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            return redirect()->route('test.take', $existingAttempt);
        }

        // Create new test attempt
        $testAttempt = TestAttempt::create([
            'user_id' => $user->id,
            'career_test_id' => $careerTest->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        return redirect()->route('test.take', $testAttempt);
    }

    /**
     * Display the test questions.
     */
    public function take(TestAttempt $testAttempt)
    {
        // Ensure the test attempt belongs to the authenticated user
        if ($testAttempt->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if test is already completed
        if ($testAttempt->status === 'completed') {
            return redirect()->route('test.result', $testAttempt);
        }

        $questions = $testAttempt->careerTest->questions;
        $currentQuestionIndex = $testAttempt->answers()->count();

        if ($currentQuestionIndex >= $questions->count()) {
            // All questions answered, complete the test
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
            'score' => 'nullable|integer|min:1|max:10',
        ]);

        // Ensure the test attempt belongs to the authenticated user
        if ($testAttempt->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if test is already completed
        if ($testAttempt->status === 'completed') {
            return redirect()->route('test.result', $testAttempt);
        }

        // Save the answer
        Answer::create([
            'test_attempt_id' => $testAttempt->id,
            'question_id' => $request->question_id,
            'answer_text' => $request->answer_text,
            'score' => $request->score,
        ]);

        return redirect()->route('test.take', $testAttempt);
    }

    /**
     * Complete the test and generate results.
     */
    private function completeTest(TestAttempt $testAttempt)
    {
        DB::transaction(function () use ($testAttempt) {
            // Mark test as completed
            $testAttempt->update([
                'completed_at' => now(),
                'status' => 'completed',
            ]);

            // Generate career result
            $this->generateCareerResult($testAttempt);
        });

        return redirect()->route('test.result', $testAttempt);
    }

    /**
     * Generate career result based on answers.
     */
    private function generateCareerResult(TestAttempt $testAttempt)
    {
        $answers = $testAttempt->answers()->with('question')->get();

        // Simple career matching algorithm
        $careerScores = [
            'Software Engineer' => 0,
            'Data Scientist' => 0,
            'Marketing Manager' => 0,
            'Teacher' => 0,
            'Doctor' => 0,
            'Artist' => 0,
        ];

        foreach ($answers as $answer) {
            $questionType = $answer->question->question_type;
            $answerText = strtolower($answer->answer_text);

            // Simple keyword matching for career suggestions
            if (str_contains($answerText, 'programming') || str_contains($answerText, 'coding') || str_contains($answerText, 'computer')) {
                $careerScores['Software Engineer'] += 2;
            }
            if (str_contains($answerText, 'data') || str_contains($answerText, 'analysis') || str_contains($answerText, 'statistics')) {
                $careerScores['Data Scientist'] += 2;
            }
            if (str_contains($answerText, 'marketing') || str_contains($answerText, 'business') || str_contains($answerText, 'sales')) {
                $careerScores['Marketing Manager'] += 2;
            }
            if (str_contains($answerText, 'teaching') || str_contains($answerText, 'education') || str_contains($answerText, 'helping')) {
                $careerScores['Teacher'] += 2;
            }
            if (str_contains($answerText, 'medicine') || str_contains($answerText, 'health') || str_contains($answerText, 'helping people')) {
                $careerScores['Doctor'] += 2;
            }
            if (str_contains($answerText, 'art') || str_contains($answerText, 'creative') || str_contains($answerText, 'design')) {
                $careerScores['Artist'] += 2;
            }

            // Add score for scale questions
            if ($questionType === 'scale' && $answer->score) {
                $careerScores['Software Engineer'] += $answer->score * 0.1;
                $careerScores['Data Scientist'] += $answer->score * 0.1;
                $careerScores['Marketing Manager'] += $answer->score * 0.1;
                $careerScores['Teacher'] += $answer->score * 0.1;
                $careerScores['Doctor'] += $answer->score * 0.1;
                $careerScores['Artist'] += $answer->score * 0.1;
            }
        }

        // Find the career with highest score
        $topCareer = array_keys($careerScores, max($careerScores))[0];
        $maxScore = max($careerScores);
        $totalPossibleScore = count($answers) * 2 + count($answers) * 1; // Max possible score
        $matchPercentage = min(100, round(($maxScore / $totalPossibleScore) * 100));

        // Create career result
        CareerResult::create([
            'test_attempt_id' => $testAttempt->id,
            'career_title' => $topCareer,
            'career_description' => $this->getCareerDescription($topCareer),
            'career_skills' => $this->getCareerSkills($topCareer),
            'career_paths' => $this->getCareerPaths($topCareer),
            'match_percentage' => $matchPercentage,
            'detailed_analysis' => $careerScores,
        ]);
    }

    /**
     * Get career description.
     */
    private function getCareerDescription($career)
    {
        $descriptions = [
            'Software Engineer' => 'Software engineers design, develop, and maintain software applications. They work with programming languages, databases, and various technologies to create solutions for businesses and users.',
            'Data Scientist' => 'Data scientists analyze complex data to help organizations make informed decisions. They use statistical methods, machine learning, and programming to extract insights from data.',
            'Marketing Manager' => 'Marketing managers develop and implement marketing strategies to promote products or services. They oversee campaigns, analyze market trends, and work with creative teams.',
            'Teacher' => 'Teachers educate and inspire students in various subjects. They create lesson plans, assess student progress, and help develop critical thinking and problem-solving skills.',
            'Doctor' => 'Doctors diagnose and treat illnesses, injuries, and medical conditions. They work to improve patient health and well-being through medical care and treatment.',
            'Artist' => 'Artists create visual, musical, or performing works of art. They express ideas, emotions, and concepts through various artistic mediums and techniques.',
        ];

        return $descriptions[$career] ?? 'A career that matches your interests and skills.';
    }

    /**
     * Get career skills.
     */
    private function getCareerSkills($career)
    {
        $skills = [
            'Software Engineer' => ['Programming', 'Problem Solving', 'System Design', 'Database Management', 'Version Control'],
            'Data Scientist' => ['Statistics', 'Machine Learning', 'Data Analysis', 'Programming', 'Data Visualization'],
            'Marketing Manager' => ['Strategic Planning', 'Communication', 'Market Research', 'Digital Marketing', 'Project Management'],
            'Teacher' => ['Communication', 'Patience', 'Subject Knowledge', 'Classroom Management', 'Assessment'],
            'Doctor' => ['Medical Knowledge', 'Diagnosis', 'Patient Care', 'Communication', 'Critical Thinking'],
            'Artist' => ['Creativity', 'Visual Design', 'Technical Skills', 'Artistic Vision', 'Communication'],
        ];

        return $skills[$career] ?? ['Communication', 'Problem Solving', 'Creativity'];
    }

    /**
     * Get career paths.
     */
    private function getCareerPaths($career)
    {
        $paths = [
            'Software Engineer' => ['Junior Developer', 'Senior Developer', 'Tech Lead', 'Software Architect', 'CTO'],
            'Data Scientist' => ['Junior Data Scientist', 'Data Scientist', 'Senior Data Scientist', 'Data Science Manager', 'Chief Data Officer'],
            'Marketing Manager' => ['Marketing Coordinator', 'Marketing Specialist', 'Marketing Manager', 'Senior Marketing Manager', 'CMO'],
            'Teacher' => ['Assistant Teacher', 'Teacher', 'Senior Teacher', 'Department Head', 'Principal'],
            'Doctor' => ['Resident', 'Attending Physician', 'Specialist', 'Department Head', 'Chief Medical Officer'],
            'Artist' => ['Freelance Artist', 'Studio Artist', 'Art Director', 'Creative Director', 'Gallery Owner'],
        ];

        return $paths[$career] ?? ['Entry Level', 'Mid Level', 'Senior Level', 'Management', 'Executive'];
    }
}
