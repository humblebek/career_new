<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\CareerTest;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;

class CareerTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // =================================================================
        // Seed careers into the careers table
        // =================================================================
        $careersData = [
            [
                'title'       => 'Software Engineer',
                'description' => 'Software engineers design, develop, and maintain software applications. They work with programming languages, databases, and various technologies to create solutions for businesses and users.',
                'skills'      => ['Programming', 'Problem Solving', 'System Design', 'Database Management', 'Version Control'],
                'paths'       => ['Junior Developer', 'Senior Developer', 'Tech Lead', 'Software Architect', 'CTO'],
            ],
            [
                'title'       => 'Data Scientist',
                'description' => 'Data scientists analyze complex data to help organizations make informed decisions. They use statistical methods, machine learning, and programming to extract insights from data.',
                'skills'      => ['Statistics', 'Machine Learning', 'Data Analysis', 'Programming', 'Data Visualization'],
                'paths'       => ['Junior Data Scientist', 'Data Scientist', 'Senior Data Scientist', 'Data Science Manager', 'Chief Data Officer'],
            ],
            [
                'title'       => 'Marketing Manager',
                'description' => 'Marketing managers develop and implement marketing strategies to promote products or services. They oversee campaigns, analyze market trends, and work with creative teams.',
                'skills'      => ['Strategic Planning', 'Communication', 'Market Research', 'Digital Marketing', 'Project Management'],
                'paths'       => ['Marketing Coordinator', 'Marketing Specialist', 'Marketing Manager', 'Senior Marketing Manager', 'CMO'],
            ],
            [
                'title'       => 'Teacher',
                'description' => 'Teachers educate and inspire students in various subjects. They create lesson plans, assess student progress, and help develop critical thinking and problem-solving skills.',
                'skills'      => ['Communication', 'Patience', 'Subject Knowledge', 'Classroom Management', 'Assessment'],
                'paths'       => ['Assistant Teacher', 'Teacher', 'Senior Teacher', 'Department Head', 'Principal'],
            ],
            [
                'title'       => 'Doctor',
                'description' => 'Doctors diagnose and treat illnesses, injuries, and medical conditions. They work to improve patient health and well-being through medical care and treatment.',
                'skills'      => ['Medical Knowledge', 'Diagnosis', 'Patient Care', 'Communication', 'Critical Thinking'],
                'paths'       => ['Resident', 'Attending Physician', 'Specialist', 'Department Head', 'Chief Medical Officer'],
            ],
            [
                'title'       => 'Artist',
                'description' => 'Artists create visual, musical, or performing works of art. They express ideas, emotions, and concepts through various artistic mediums and techniques.',
                'skills'      => ['Creativity', 'Visual Design', 'Technical Skills', 'Artistic Vision', 'Communication'],
                'paths'       => ['Freelance Artist', 'Studio Artist', 'Art Director', 'Creative Director', 'Gallery Owner'],
            ],
        ];

        foreach ($careersData as $c) {
            Career::firstOrCreate(['title' => $c['title']], $c);
        }

        // =================================================================
        // Create users
        // =================================================================
        $admin = User::firstOrCreate(
            ['email' => 'admin@careerpath.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('admin123'),
                'secret_word' => bcrypt('admin'),
                'role' => 'admin',
            ]
        );

        $student = User::firstOrCreate(
            ['email' => 'student@careerpath.com'],
            [
                'name' => 'John Doe',
                'password' => bcrypt('student123'),
                'secret_word' => bcrypt('student'),
                'role' => 'student',
            ]
        );

        // =================================================================
        // Comprehensive Career Assessment
        // =================================================================
        $test = CareerTest::firstOrCreate(
            ['title' => 'Comprehensive Career Assessment'],
            [
                'description' => 'A comprehensive assessment to help you discover your ideal career path based on your interests, skills, and personality traits.',
                'duration_minutes' => 30,
                'is_active' => true,
            ]
        );

        $questions = [
            [
                'question_text' => 'What type of activities do you enjoy most?',
                'question_type' => 'multiple_choice',
                'options' => ['Working with computers and technology', 'Helping and teaching others', 'Creating art or music', 'Analyzing data and solving problems', 'Leading teams and managing projects'],
                'career_weights' => [
                    'options' => [
                        ['Software Engineer' => 3, 'Data Scientist' => 1],
                        ['Teacher' => 3, 'Doctor' => 1],
                        ['Artist' => 3],
                        ['Data Scientist' => 3, 'Software Engineer' => 1],
                        ['Marketing Manager' => 3],
                    ],
                ],
                'category'   => 'interests',
                'importance' => 1.2,
                'order'      => 1,
            ],
            [
                'question_text' => 'How much do you enjoy working with numbers and data?',
                'question_type' => 'scale',
                'options' => null,
                'career_weights' => [
                    'careers' => [
                        'Data Scientist'    => 0.8,
                        'Software Engineer' => 0.5,
                        'Marketing Manager' => 0.2,
                        'Teacher'           => 0.1,
                        'Doctor'            => 0.2,
                        'Artist'            => 0.0,
                    ],
                ],
                'category'   => 'skills',
                'importance' => 1.0,
                'order'      => 2,
            ],
            [
                'question_text' => 'Describe your ideal work environment.',
                'question_type' => 'short_answer',
                'options' => null,
                'career_weights' => [
                    'keywords' => [
                        'office'      => ['Software Engineer' => 1, 'Marketing Manager' => 1],
                        'remote'      => ['Software Engineer' => 2, 'Data Scientist' => 1],
                        'lab'         => ['Data Scientist' => 2, 'Doctor' => 2],
                        'hospital'    => ['Doctor' => 3],
                        'school'      => ['Teacher' => 3],
                        'classroom'   => ['Teacher' => 3],
                        'studio'      => ['Artist' => 3],
                        'outdoor'     => ['Doctor' => 1, 'Teacher' => 1],
                        'creative'    => ['Artist' => 2, 'Marketing Manager' => 1],
                        'collaborative' => ['Marketing Manager' => 2, 'Teacher' => 1],
                        'quiet'       => ['Data Scientist' => 1, 'Software Engineer' => 1],
                    ],
                ],
                'category'   => 'personality',
                'importance' => 0.8,
                'order'      => 3,
            ],
            [
                'question_text' => 'What motivates you most in a job?',
                'question_type' => 'multiple_choice',
                'options' => ['High salary and benefits', 'Making a positive impact', 'Creative freedom', 'Job security', 'Career advancement opportunities'],
                'career_weights' => [
                    'options' => [
                        ['Software Engineer' => 2, 'Data Scientist' => 2],
                        ['Teacher' => 3, 'Doctor' => 3],
                        ['Artist' => 3, 'Marketing Manager' => 1],
                        ['Doctor' => 2, 'Teacher' => 2],
                        ['Marketing Manager' => 3, 'Software Engineer' => 1],
                    ],
                ],
                'category'   => 'goals',
                'importance' => 1.0,
                'order'      => 4,
            ],
            [
                'question_text' => 'How comfortable are you with public speaking and presentations?',
                'question_type' => 'scale',
                'options' => null,
                'career_weights' => [
                    'careers' => [
                        'Marketing Manager' => 0.8,
                        'Teacher'           => 0.7,
                        'Doctor'            => 0.3,
                        'Software Engineer' => 0.1,
                        'Data Scientist'    => 0.1,
                        'Artist'            => 0.3,
                    ],
                ],
                'category'   => 'skills',
                'importance' => 1.0,
                'order'      => 5,
            ],
            [
                'question_text' => 'What subjects did you enjoy most in school?',
                'question_type' => 'multiple_choice',
                'options' => ['Mathematics and Science', 'English and Literature', 'Art and Music', 'History and Social Studies', 'Physical Education'],
                'career_weights' => [
                    'options' => [
                        ['Software Engineer' => 3, 'Data Scientist' => 3, 'Doctor' => 1],
                        ['Teacher' => 2, 'Marketing Manager' => 1],
                        ['Artist' => 3],
                        ['Teacher' => 2, 'Marketing Manager' => 1],
                        ['Doctor' => 1, 'Teacher' => 1],
                    ],
                ],
                'category'   => 'interests',
                'importance' => 1.2,
                'order'      => 6,
            ],
            [
                'question_text' => 'Describe a project or task you completed that you were proud of.',
                'question_type' => 'short_answer',
                'options' => null,
                'career_weights' => [
                    'keywords' => [
                        'app'           => ['Software Engineer' => 3],
                        'website'       => ['Software Engineer' => 3, 'Marketing Manager' => 1],
                        'model'         => ['Data Scientist' => 3, 'Software Engineer' => 1],
                        'analysis'      => ['Data Scientist' => 3],
                        'campaign'      => ['Marketing Manager' => 3],
                        'taught'        => ['Teacher' => 3],
                        'lesson'        => ['Teacher' => 3],
                        'patient'       => ['Doctor' => 3],
                        'painting'      => ['Artist' => 3],
                        'design'        => ['Artist' => 2, 'Software Engineer' => 1],
                        'research'      => ['Data Scientist' => 2, 'Doctor' => 2],
                        'program'       => ['Software Engineer' => 2],
                        'project'       => ['Software Engineer' => 1, 'Marketing Manager' => 1],
                    ],
                ],
                'category'   => 'skills',
                'importance' => 0.8,
                'order'      => 7,
            ],
            [
                'question_text' => 'How important is work-life balance to you?',
                'question_type' => 'scale',
                'options' => null,
                'career_weights' => [
                    'careers' => [
                        'Teacher'           => 0.4,
                        'Artist'            => 0.4,
                        'Marketing Manager' => 0.3,
                        'Software Engineer' => 0.3,
                        'Data Scientist'    => 0.3,
                        'Doctor'            => 0.1,
                    ],
                ],
                'category'   => 'personality',
                'importance' => 0.6,
                'order'      => 8,
            ],
            [
                'question_text' => 'What type of problems do you enjoy solving?',
                'question_type' => 'multiple_choice',
                'options' => ['Technical and logical problems', 'Social and interpersonal issues', 'Creative and artistic challenges', 'Business and financial problems', 'Research and analytical questions'],
                'career_weights' => [
                    'options' => [
                        ['Software Engineer' => 3, 'Data Scientist' => 1],
                        ['Teacher' => 3, 'Doctor' => 2],
                        ['Artist' => 3, 'Marketing Manager' => 1],
                        ['Marketing Manager' => 3],
                        ['Data Scientist' => 3, 'Doctor' => 1],
                    ],
                ],
                'category'   => 'interests',
                'importance' => 1.2,
                'order'      => 9,
            ],
            [
                'question_text' => 'Where do you see yourself in 10 years?',
                'question_type' => 'short_answer',
                'options' => null,
                'career_weights' => [
                    'keywords' => [
                        'cto'           => ['Software Engineer' => 3],
                        'developer'     => ['Software Engineer' => 2],
                        'data'          => ['Data Scientist' => 2],
                        'scientist'     => ['Data Scientist' => 3],
                        'marketing'     => ['Marketing Manager' => 3],
                        'manager'       => ['Marketing Manager' => 2],
                        'teacher'       => ['Teacher' => 3],
                        'professor'     => ['Teacher' => 3],
                        'doctor'        => ['Doctor' => 3],
                        'surgeon'       => ['Doctor' => 3],
                        'artist'        => ['Artist' => 3],
                        'designer'      => ['Artist' => 2, 'Software Engineer' => 1],
                        'entrepreneur'  => ['Marketing Manager' => 2, 'Software Engineer' => 1],
                        'running my own' => ['Marketing Manager' => 2, 'Artist' => 1],
                    ],
                ],
                'category'   => 'goals',
                'importance' => 1.0,
                'order'      => 10,
            ],
        ];

        foreach ($questions as $qData) {
            $existing = Question::where('career_test_id', $test->id)
                ->where('question_text', $qData['question_text'])
                ->first();

            if ($existing) {
                $existing->update([
                    'career_weights' => $qData['career_weights'],
                    'category'       => $qData['category'],
                    'importance'     => $qData['importance'],
                ]);
            } else {
                Question::create(array_merge($qData, ['career_test_id' => $test->id]));
            }
        }

        // =================================================================
        // STEM Career Assessment
        // =================================================================
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
                'career_weights' => [
                    'options' => [
                        ['Data Scientist' => 3, 'Software Engineer' => 2],
                        ['Software Engineer' => 3, 'Marketing Manager' => 1],
                        ['Software Engineer' => 3],
                        ['Software Engineer' => 3],
                        ['Teacher' => 1, 'Artist' => 1],
                    ],
                ],
                'category'   => 'skills',
                'importance' => 1.2,
                'order'      => 1,
            ],
            [
                'question_text' => 'How interested are you in artificial intelligence and machine learning?',
                'question_type' => 'scale',
                'options' => null,
                'career_weights' => [
                    'careers' => [
                        'Data Scientist'    => 0.9,
                        'Software Engineer' => 0.6,
                        'Doctor'            => 0.2,
                        'Marketing Manager' => 0.1,
                        'Teacher'           => 0.1,
                        'Artist'            => 0.0,
                    ],
                ],
                'category'   => 'interests',
                'importance' => 1.0,
                'order'      => 2,
            ],
            [
                'question_text' => 'What type of engineering field appeals to you?',
                'question_type' => 'multiple_choice',
                'options' => [
                    'Software Engineering',
                    'Mechanical Engineering',
                    'Civil Engineering',
                    'Electrical Engineering',
                    'Biomedical Engineering',
                ],
                'career_weights' => [
                    'options' => [
                        ['Software Engineer' => 3, 'Data Scientist' => 1],
                        ['Software Engineer' => 2],
                        ['Software Engineer' => 1],
                        ['Software Engineer' => 2, 'Data Scientist' => 1],
                        ['Doctor' => 2, 'Data Scientist' => 1],
                    ],
                ],
                'category'   => 'interests',
                'importance' => 1.0,
                'order'      => 3,
            ],
            [
                'question_text' => 'Describe your experience with mathematics.',
                'question_type' => 'short_answer',
                'options' => null,
                'career_weights' => [
                    'keywords' => [
                        'calculus'     => ['Data Scientist' => 3, 'Software Engineer' => 2],
                        'statistics'   => ['Data Scientist' => 3],
                        'algebra'      => ['Data Scientist' => 2, 'Software Engineer' => 1],
                        'geometry'     => ['Software Engineer' => 1, 'Artist' => 1],
                        'love'         => ['Data Scientist' => 1, 'Software Engineer' => 1],
                        'enjoy'        => ['Data Scientist' => 1, 'Software Engineer' => 1],
                        'difficult'    => ['Teacher' => 1],
                        'teaching'     => ['Teacher' => 2],
                        'research'     => ['Data Scientist' => 2],
                    ],
                ],
                'category'   => 'skills',
                'importance' => 0.8,
                'order'      => 4,
            ],
        ];

        foreach ($stemQuestions as $qData) {
            $existing = Question::where('career_test_id', $test2->id)
                ->where('question_text', $qData['question_text'])
                ->first();

            if ($existing) {
                $existing->update([
                    'career_weights' => $qData['career_weights'],
                    'category'       => $qData['category'],
                    'importance'     => $qData['importance'],
                ]);
            } else {
                Question::create(array_merge($qData, ['career_test_id' => $test2->id]));
            }
        }
    }
}
