<?php

namespace Database\Seeders;

use App\Models\CareerTest;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CareerTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user (if not exists)
        $admin = User::firstOrCreate(
            ['email' => 'admin@careerpath.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // Create sample student (if not exists)
        $student = User::firstOrCreate(
            ['email' => 'student@careerpath.com'],
            [
                'name' => 'John Student',
                'password' => bcrypt('password'),
                'role' => 'student',
            ]
        );

        // Create career test (if not exists)
        $test = CareerTest::firstOrCreate(
            ['title' => 'Comprehensive Career Assessment'],
            [
                'description' => 'A comprehensive assessment to help you discover your ideal career path based on your interests, skills, and personality traits.',
                'duration_minutes' => 30,
                'is_active' => true,
            ]
        );

        // Create questions
        $questions = [
            [
                'question_text' => 'What type of activities do you enjoy most?',
                'question_type' => 'multiple_choice',
                'options' => ['Working with computers and technology', 'Helping and teaching others', 'Creating art or music', 'Analyzing data and solving problems', 'Leading teams and managing projects'],
                'order' => 1,
            ],
            [
                'question_text' => 'How much do you enjoy working with numbers and data?',
                'question_type' => 'scale',
                'options' => null,
                'order' => 2,
            ],
            [
                'question_text' => 'Describe your ideal work environment.',
                'question_type' => 'short_answer',
                'options' => null,
                'order' => 3,
            ],
            [
                'question_text' => 'What motivates you most in a job?',
                'question_type' => 'multiple_choice',
                'options' => ['High salary and benefits', 'Making a positive impact', 'Creative freedom', 'Job security', 'Career advancement opportunities'],
                'order' => 4,
            ],
            [
                'question_text' => 'How comfortable are you with public speaking and presentations?',
                'question_type' => 'scale',
                'options' => null,
                'order' => 5,
            ],
            [
                'question_text' => 'What subjects did you enjoy most in school?',
                'question_type' => 'multiple_choice',
                'options' => ['Mathematics and Science', 'English and Literature', 'Art and Music', 'History and Social Studies', 'Physical Education'],
                'order' => 6,
            ],
            [
                'question_text' => 'Describe a project or task you completed that you were proud of.',
                'question_type' => 'short_answer',
                'options' => null,
                'order' => 7,
            ],
            [
                'question_text' => 'How important is work-life balance to you?',
                'question_type' => 'scale',
                'options' => null,
                'order' => 8,
            ],
            [
                'question_text' => 'What type of problems do you enjoy solving?',
                'question_type' => 'multiple_choice',
                'options' => ['Technical and logical problems', 'Social and interpersonal issues', 'Creative and artistic challenges', 'Business and financial problems', 'Research and analytical questions'],
                'order' => 9,
            ],
            [
                'question_text' => 'Where do you see yourself in 10 years?',
                'question_type' => 'short_answer',
                'options' => null,
                'order' => 10,
            ],
        ];

        foreach ($questions as $questionData) {
            Question::firstOrCreate(
                [
                    'career_test_id' => $test->id,
                    'question_text' => $questionData['question_text'],
                ],
                [
                    'question_type' => $questionData['question_type'],
                    'options' => $questionData['options'],
                    'order' => $questionData['order'],
                ]
            );
        }

        // Create another test (if not exists)
        $test2 = CareerTest::firstOrCreate(
            ['title' => 'STEM Career Assessment'],
            [
                'description' => 'Specialized assessment for students interested in Science, Technology, Engineering, and Mathematics careers.',
                'duration_minutes' => 20,
                'is_active' => true,
            ]
        );

        $stemQuestions = [
            [
                'question_text' => 'Which programming language interests you most?',
                'question_type' => 'multiple_choice',
                'options' => ['Python', 'JavaScript', 'Java', 'C++', 'None of the above'],
                'order' => 1,
            ],
            [
                'question_text' => 'How interested are you in artificial intelligence and machine learning?',
                'question_type' => 'scale',
                'options' => null,
                'order' => 2,
            ],
            [
                'question_text' => 'What type of engineering field appeals to you?',
                'question_type' => 'multiple_choice',
                'options' => ['Software Engineering', 'Mechanical Engineering', 'Civil Engineering', 'Electrical Engineering', 'Biomedical Engineering'],
                'order' => 3,
            ],
            [
                'question_text' => 'Describe your experience with mathematics.',
                'question_type' => 'short_answer',
                'options' => null,
                'order' => 4,
            ],
        ];

        foreach ($stemQuestions as $questionData) {
            Question::firstOrCreate(
                [
                    'career_test_id' => $test2->id,
                    'question_text' => $questionData['question_text'],
                ],
                [
                    'question_type' => $questionData['question_type'],
                    'options' => $questionData['options'],
                    'order' => $questionData['order'],
                ]
            );
        }
    }
}
