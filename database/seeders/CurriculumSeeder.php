<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * CurriculumSeeder
 * -----------------
 * Seeds a published Course (with lesson plan + starter quiz) for every
 * O-Level and A-Level subject defined in resources/data/subjects.php,
 * aligned to the ZIMSEC syllabus topic list.
 *
 * Idempotent: re-running will not duplicate courses, lessons or questions
 * (uses firstOrCreate on natural keys).
 */
class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = require base_path('resources/data/subjects.php');
        $teacher  = $this->ensureCurriculumTeacher();

        foreach (['o_level' => 'Form 4 (O-Level)', 'a_level' => 'Form 6 (A-Level)'] as $tier => $gradeLevel) {
            foreach ($subjects[$tier] ?? [] as $subject) {
                $this->seedSubject($teacher, $subject, $tier, $gradeLevel);
            }
        }
    }

    protected function ensureCurriculumTeacher(): User
    {
        $teacher = User::firstOrCreate(
            ['email' => 'curriculum@edubridge.co.zw'],
            [
                'name'     => 'EduBridge Curriculum Team',
                'password' => Hash::make(\Illuminate\Support\Str::random(32)),
                'role'     => 'teacher',
                'country'  => 'ZW',
                'bio'      => 'Official ZIMSEC syllabus-aligned curriculum maintained by the EduBridge team.',
            ]
        );

        if (! $teacher->hasRole('teacher')) {
            $teacher->assignRole('teacher');
        }

        return $teacher;
    }

    protected function seedSubject(User $teacher, array $subject, string $tier, string $gradeLevel): void
    {
        $isALevel = $tier === 'a_level';
        $title    = sprintf('%s — %s', $subject['name'], $isALevel ? 'A-Level' : 'O-Level');

        $course = Course::firstOrCreate(
            ['title' => $title, 'teacher_id' => $teacher->id],
            [
                'subject'     => $subject['name'],
                'grade_level' => $gradeLevel,
                'description' => $this->buildCourseDescription($subject, $isALevel),
                'status'      => 'published',
                'price_usd'   => $isALevel ? 8.00 : 5.00,
                'price_zwg'   => $isALevel ? 290.00 : 180.00,
            ]
        );

        // Re-publish in case it was set to draft previously.
        if ($course->status !== 'published') {
            $course->update(['status' => 'published']);
        }

        $topics = $subject['topics'] ?? [];
        if (empty($topics)) {
            return;
        }

        $order = 0;
        foreach ($topics as $topic) {
            $order++;
            Lesson::firstOrCreate(
                ['course_id' => $course->id, 'title' => $topic],
                [
                    'description'      => $this->buildLessonDescription($subject['name'], $topic, $isALevel),
                    'order'            => $order,
                    'status'           => 'published',
                    'duration_seconds' => 1800, // 30-minute target per lesson
                ]
            );
        }

        $this->seedQuiz($teacher, $course, $subject, $isALevel);
    }

    protected function buildCourseDescription(array $subject, bool $isALevel): string
    {
        $level   = $isALevel ? 'A-Level' : 'O-Level';
        $topics  = implode(', ', $subject['topics'] ?? []);
        $paper   = ! empty($subject['paper_code']) ? " (ZIMSEC paper {$subject['paper_code']})" : '';
        $intro   = $subject['desc'] ?? '';

        return trim("ZIMSEC {$level} {$subject['name']}{$paper}. {$intro} Syllabus-aligned coverage: {$topics}.");
    }

    protected function buildLessonDescription(string $subjectName, string $topic, bool $isALevel): string
    {
        $level = $isALevel ? 'A-Level' : 'O-Level';

        return "{$level} {$subjectName} — {$topic}. Covers key definitions, worked examples, common exam questions and revision pointers aligned to the ZIMSEC syllabus.";
    }

    protected function seedQuiz(User $teacher, Course $course, array $subject, bool $isALevel): void
    {
        $quizTitle = "{$subject['name']} — Diagnostic Quiz";

        $quiz = Quiz::firstOrCreate(
            ['course_id' => $course->id, 'title' => $quizTitle],
            [
                'teacher_id'         => $teacher->id,
                'description'        => "Quick diagnostic covering the {$subject['name']} syllabus topics. Use it to gauge readiness before deeper study.",
                'time_limit_minutes' => 15,
                'pass_percentage'    => 60,
                'max_attempts'       => 5,
                'show_answers_after' => true,
                'is_published'       => true,
            ]
        );

        // Avoid re-seeding questions if they already exist.
        if ($quiz->questions()->exists()) {
            return;
        }

        $questions = $this->buildQuestions($subject, $isALevel);
        foreach ($questions as $i => $q) {
            QuizQuestion::create(array_merge($q, [
                'quiz_id'    => $quiz->id,
                'sort_order' => $i + 1,
                'points'     => 1,
            ]));
        }
    }

    /**
     * Build a list of MCQ questions for the subject. Uses curated questions
     * when available, otherwise falls back to a generic syllabus-topic prompt.
     */
    protected function buildQuestions(array $subject, bool $isALevel): array
    {
        $curated = $this->curatedQuestions()[$subject['name']] ?? [];
        if (! empty($curated)) {
            return $curated;
        }

        // Fallback: one MCQ per topic asking which area the topic belongs to.
        $out = [];
        foreach ($subject['topics'] ?? [] as $topic) {
            $out[] = [
                'type'           => 'mcq',
                'question'       => "Which of the following best describes the focus of \"{$topic}\" in {$subject['name']}?",
                'options'        => [
                    "A core syllabus topic in {$subject['name']}",
                    "An unrelated language skill",
                    "A pure sporting activity",
                    "A purely religious practice",
                ],
                'correct_answer' => "A core syllabus topic in {$subject['name']}",
                'explanation'    => "{$topic} is part of the ZIMSEC syllabus for {$subject['name']}.",
            ];
        }

        return $out;
    }

    /**
     * Curated MCQ bank for the most-taught subjects. Each item is shaped
     * exactly like a QuizQuestion row (minus quiz_id / sort_order / points).
     */
    protected function curatedQuestions(): array
    {
        return [
            'Mathematics' => [
                [
                    'type' => 'mcq',
                    'question' => 'Solve for x: 2x + 5 = 17',
                    'options' => ['x = 4', 'x = 6', 'x = 8', 'x = 11'],
                    'correct_answer' => 'x = 6',
                    'explanation' => '2x = 12, so x = 6.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'Which is the quadratic formula?',
                    'options' => [
                        'x = (-b ± √(b² - 4ac)) / 2a',
                        'x = (b ± √(b² + 4ac)) / 2a',
                        'x = -b / 2a',
                        'x = b² - 4ac',
                    ],
                    'correct_answer' => 'x = (-b ± √(b² - 4ac)) / 2a',
                    'explanation' => 'Standard quadratic formula for ax² + bx + c = 0.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'The angle subtended by a diameter at the circumference is:',
                    'options' => ['45°', '60°', '90°', '180°'],
                    'correct_answer' => '90°',
                    'explanation' => 'Angle in a semicircle is a right angle (circle theorem).',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'The median of 3, 7, 8, 5, 12 is:',
                    'options' => ['5', '7', '8', '12'],
                    'correct_answer' => '7',
                    'explanation' => 'Ordered: 3,5,7,8,12. Middle value is 7.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'sin(30°) equals:',
                    'options' => ['0', '1/2', '√3/2', '1'],
                    'correct_answer' => '1/2',
                    'explanation' => 'Standard trig value.',
                ],
            ],
            'Physics' => [
                [
                    'type' => 'mcq',
                    'question' => 'The SI unit of force is the:',
                    'options' => ['Joule', 'Watt', 'Newton', 'Pascal'],
                    'correct_answer' => 'Newton',
                    'explanation' => '1 N = 1 kg·m/s².',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'Which formula gives kinetic energy?',
                    'options' => ['mgh', '½mv²', 'F·d', 'P·t'],
                    'correct_answer' => '½mv²',
                    'explanation' => 'KE = ½ × mass × velocity².',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'Ohm\'s law states that:',
                    'options' => ['V = IR', 'P = IV', 'F = ma', 'E = mc²'],
                    'correct_answer' => 'V = IR',
                    'explanation' => 'Voltage equals current times resistance.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'Sound is a:',
                    'options' => ['Transverse wave', 'Longitudinal wave', 'Electromagnetic wave', 'Standing wave only'],
                    'correct_answer' => 'Longitudinal wave',
                    'explanation' => 'Sound travels via compressions and rarefactions.',
                ],
            ],
            'Chemistry' => [
                [
                    'type' => 'mcq',
                    'question' => 'The pH of a neutral solution at 25°C is:',
                    'options' => ['0', '7', '14', '1'],
                    'correct_answer' => '7',
                    'explanation' => 'Pure water has pH 7 at 25°C.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'Which particle has no charge?',
                    'options' => ['Proton', 'Electron', 'Neutron', 'Ion'],
                    'correct_answer' => 'Neutron',
                    'explanation' => 'Neutrons are electrically neutral.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'NaCl is held together by:',
                    'options' => ['Covalent bonds', 'Ionic bonds', 'Hydrogen bonds', 'Metallic bonds'],
                    'correct_answer' => 'Ionic bonds',
                    'explanation' => 'Na⁺ and Cl⁻ form an ionic lattice.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'The general formula of alkanes is:',
                    'options' => ['CₙH₂ₙ', 'CₙH₂ₙ₊₂', 'CₙH₂ₙ₋₂', 'CₙHₙ'],
                    'correct_answer' => 'CₙH₂ₙ₊₂',
                    'explanation' => 'Saturated hydrocarbons follow CₙH₂ₙ₊₂.',
                ],
            ],
            'Biology' => [
                [
                    'type' => 'mcq',
                    'question' => 'The basic unit of life is the:',
                    'options' => ['Atom', 'Cell', 'Tissue', 'Organ'],
                    'correct_answer' => 'Cell',
                    'explanation' => 'Cells are the structural unit of all living organisms.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'Photosynthesis takes place mainly in the:',
                    'options' => ['Mitochondria', 'Nucleus', 'Chloroplast', 'Ribosome'],
                    'correct_answer' => 'Chloroplast',
                    'explanation' => 'Chloroplasts contain chlorophyll which captures light.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'DNA is short for:',
                    'options' => ['Deoxyribonucleic Acid', 'Dinitrogen Acid', 'Dual Nuclear Acid', 'Direct Nucleotide Assembly'],
                    'correct_answer' => 'Deoxyribonucleic Acid',
                    'explanation' => 'DNA stores genetic information.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'Which organ produces insulin?',
                    'options' => ['Liver', 'Kidney', 'Pancreas', 'Stomach'],
                    'correct_answer' => 'Pancreas',
                    'explanation' => 'Beta cells of the pancreas secrete insulin.',
                ],
            ],
            'Combined Science' => [
                [
                    'type' => 'mcq',
                    'question' => 'Water boils at what temperature at sea level?',
                    'options' => ['90°C', '100°C', '110°C', '120°C'],
                    'correct_answer' => '100°C',
                    'explanation' => 'At standard atmospheric pressure.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'The gas plants take in for photosynthesis is:',
                    'options' => ['Oxygen', 'Nitrogen', 'Carbon Dioxide', 'Hydrogen'],
                    'correct_answer' => 'Carbon Dioxide',
                    'explanation' => 'CO₂ + H₂O → glucose + O₂ in sunlight.',
                ],
                [
                    'type' => 'true_false',
                    'question' => 'Force is measured in Newtons.',
                    'options' => ['True', 'False'],
                    'correct_answer' => 'True',
                    'explanation' => 'The SI unit of force is the Newton.',
                ],
            ],
            'English Language' => [
                [
                    'type' => 'mcq',
                    'question' => 'Which sentence is grammatically correct?',
                    'options' => [
                        'She don\'t like mangoes.',
                        'She doesn\'t like mangoes.',
                        'She not like mangoes.',
                        'She no like mangoes.',
                    ],
                    'correct_answer' => 'She doesn\'t like mangoes.',
                    'explanation' => 'Third-person singular requires "doesn\'t".',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'A synonym of "rapid" is:',
                    'options' => ['Slow', 'Quick', 'Heavy', 'Loud'],
                    'correct_answer' => 'Quick',
                    'explanation' => '"Quick" means happening fast.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'In a formal letter, which closing is most appropriate?',
                    'options' => ['Cheers!', 'Yours sincerely,', 'Bye for now,', 'Catch ya later,'],
                    'correct_answer' => 'Yours sincerely,',
                    'explanation' => 'Used when the recipient is named.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'The plural of "child" is:',
                    'options' => ['Childs', 'Childes', 'Children', 'Childrens'],
                    'correct_answer' => 'Children',
                    'explanation' => 'Irregular plural.',
                ],
            ],
            'Geography' => [
                [
                    'type' => 'mcq',
                    'question' => 'The Equator passes through which African country?',
                    'options' => ['Zimbabwe', 'Kenya', 'South Africa', 'Egypt'],
                    'correct_answer' => 'Kenya',
                    'explanation' => 'The Equator crosses through Kenya.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'A contour line on a map joins points of equal:',
                    'options' => ['Temperature', 'Rainfall', 'Height', 'Population'],
                    'correct_answer' => 'Height',
                    'explanation' => 'Contour lines indicate elevation.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'Plate tectonics best explains:',
                    'options' => ['Weather forecasting', 'Earthquakes and volcanoes', 'Population growth', 'Crop rotation'],
                    'correct_answer' => 'Earthquakes and volcanoes',
                    'explanation' => 'Tectonic plate movements cause both.',
                ],
            ],
            'History' => [
                [
                    'type' => 'mcq',
                    'question' => 'Zimbabwe gained independence in:',
                    'options' => ['1965', '1972', '1980', '1990'],
                    'correct_answer' => '1980',
                    'explanation' => 'Independence was on 18 April 1980.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'The First Chimurenga was fought against:',
                    'options' => ['Portuguese settlers', 'British South Africa Company', 'Boer republics', 'German colonialists'],
                    'correct_answer' => 'British South Africa Company',
                    'explanation' => 'The 1896–97 uprising was against BSAC rule.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'The Cold War was primarily between:',
                    'options' => ['Britain and France', 'USA and USSR', 'China and Japan', 'Germany and Italy'],
                    'correct_answer' => 'USA and USSR',
                    'explanation' => 'Ideological rivalry between capitalism and communism.',
                ],
            ],
            'Business Studies' => [
                [
                    'type' => 'mcq',
                    'question' => 'A sole trader business is owned by:',
                    'options' => ['One person', 'Two partners', 'Shareholders', 'Government'],
                    'correct_answer' => 'One person',
                    'explanation' => 'By definition, a sole trader is a single owner.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'The 4Ps of the marketing mix are:',
                    'options' => [
                        'People, Process, Place, Price',
                        'Product, Price, Place, Promotion',
                        'People, Plan, Profit, Product',
                        'Price, Profit, Promotion, Position',
                    ],
                    'correct_answer' => 'Product, Price, Place, Promotion',
                    'explanation' => 'Classic McCarthy 4Ps.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'Working capital equals:',
                    'options' => [
                        'Current assets − Current liabilities',
                        'Fixed assets + Cash',
                        'Sales − Costs',
                        'Capital + Profit',
                    ],
                    'correct_answer' => 'Current assets − Current liabilities',
                    'explanation' => 'Standard definition of working capital.',
                ],
            ],
            'Accounting' => [
                [
                    'type' => 'mcq',
                    'question' => 'The accounting equation is:',
                    'options' => [
                        'Assets = Liabilities + Capital',
                        'Assets = Liabilities − Capital',
                        'Capital = Assets + Liabilities',
                        'Profit = Sales + Capital',
                    ],
                    'correct_answer' => 'Assets = Liabilities + Capital',
                    'explanation' => 'Fundamental double-entry identity.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'A debit entry in a cash account represents:',
                    'options' => ['Cash paid out', 'Cash received', 'A loss', 'A liability'],
                    'correct_answer' => 'Cash received',
                    'explanation' => 'Cash account is an asset; debit increases assets.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'Depreciation is best described as:',
                    'options' => [
                        'A cash payment for assets',
                        'Allocation of asset cost over its useful life',
                        'An increase in asset value',
                        'A tax refund',
                    ],
                    'correct_answer' => 'Allocation of asset cost over its useful life',
                    'explanation' => 'Depreciation matches expense with the asset\'s useful life.',
                ],
            ],
            'Computer Science' => [
                [
                    'type' => 'mcq',
                    'question' => 'Which is NOT a programming language?',
                    'options' => ['Python', 'Java', 'HTTP', 'C++'],
                    'correct_answer' => 'HTTP',
                    'explanation' => 'HTTP is a protocol, not a programming language.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'Binary 1010 in decimal is:',
                    'options' => ['8', '9', '10', '12'],
                    'correct_answer' => '10',
                    'explanation' => '8 + 0 + 2 + 0 = 10.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'SQL is used for:',
                    'options' => ['Styling web pages', 'Querying databases', 'Network routing', '3D rendering'],
                    'correct_answer' => 'Querying databases',
                    'explanation' => 'Structured Query Language manages relational data.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'Which is volatile memory?',
                    'options' => ['ROM', 'Hard Disk', 'RAM', 'SSD'],
                    'correct_answer' => 'RAM',
                    'explanation' => 'RAM loses its contents when power is off.',
                ],
            ],
            'Agriculture' => [
                [
                    'type' => 'mcq',
                    'question' => 'Crop rotation primarily helps to:',
                    'options' => [
                        'Increase pest populations',
                        'Maintain soil fertility',
                        'Reduce yield',
                        'Waste water',
                    ],
                    'correct_answer' => 'Maintain soil fertility',
                    'explanation' => 'Rotating crops prevents nutrient depletion and breaks pest cycles.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'A ruminant animal is:',
                    'options' => ['Pig', 'Chicken', 'Cattle', 'Dog'],
                    'correct_answer' => 'Cattle',
                    'explanation' => 'Cattle have a four-chambered stomach for fermenting plant matter.',
                ],
            ],
            'Textile Technology & Design' => [
                [
                    'type' => 'mcq',
                    'question' => 'Cotton is classified as a:',
                    'options' => ['Synthetic fibre', 'Natural plant fibre', 'Animal fibre', 'Mineral fibre'],
                    'correct_answer' => 'Natural plant fibre',
                    'explanation' => 'Cotton comes from the cotton plant boll.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'A double-stitched seam is used to:',
                    'options' => ['Decorate hems', 'Strengthen seams in heavy fabric', 'Replace zips', 'Add buttons'],
                    'correct_answer' => 'Strengthen seams in heavy fabric',
                    'explanation' => 'Two parallel lines of stitching reinforce the seam.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'A grain line marking on a pattern shows:',
                    'options' => ['Where to cut darts', 'Direction of fabric weave', 'Hem allowance', 'Button placement'],
                    'correct_answer' => 'Direction of fabric weave',
                    'explanation' => 'Grain lines align the pattern with the warp threads.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'Which is a safety precaution in the sewing workshop?',
                    'options' => [
                        'Run with scissors',
                        'Keep cords across walkways',
                        'Switch off machines before threading',
                        'Eat at the machine',
                    ],
                    'correct_answer' => 'Switch off machines before threading',
                    'explanation' => 'Prevents accidental needle injuries.',
                ],
            ],
            'Economics' => [
                [
                    'type' => 'mcq',
                    'question' => 'Inflation refers to:',
                    'options' => [
                        'A fall in unemployment',
                        'A sustained rise in the general price level',
                        'An increase in exports',
                        'A drop in interest rates',
                    ],
                    'correct_answer' => 'A sustained rise in the general price level',
                    'explanation' => 'Standard macroeconomic definition.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'GDP stands for:',
                    'options' => [
                        'General Domestic Pricing',
                        'Gross Domestic Product',
                        'Global Development Plan',
                        'Government Debt Programme',
                    ],
                    'correct_answer' => 'Gross Domestic Product',
                    'explanation' => 'Total value of goods/services produced in a country.',
                ],
                [
                    'type' => 'mcq',
                    'question' => 'The law of demand states that, all else equal, as price rises:',
                    'options' => [
                        'Quantity demanded rises',
                        'Quantity demanded falls',
                        'Supply falls',
                        'Income rises',
                    ],
                    'correct_answer' => 'Quantity demanded falls',
                    'explanation' => 'Inverse relationship between price and quantity demanded.',
                ],
            ],
        ];
    }
}
