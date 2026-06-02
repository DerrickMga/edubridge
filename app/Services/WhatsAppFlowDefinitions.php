<?php

namespace App\Services;

/**
 * WhatsAppFlowDefinitions
 *
 * Builds WhatsApp Flows JSON (v6.3) for EduBridge / ChiedzaByKMG.
 *
 * Template variable syntax: ${data.x} and ${form.x}
 * These are WhatsApp Flow expressions — NOT PHP variables.
 * Written as "\${data.x}" in PHP double-quoted strings.
 *
 * Flows:
 *   chiedza_register  — static sign-up (SIGN_UP, no endpoint)
 *   chiedza_student   — data-exchange student hub (EDUCATION)
 *   chiedza_teacher   — data-exchange teacher hub (EDUCATION)
 */
class WhatsAppFlowDefinitions
{
    // ─────────────────────────────────────────────────────────────────────────
    //  Flow: chiedza_register  (STATIC)
    // ─────────────────────────────────────────────────────────────────────────

    public static function registerFlow(): array
    {
        return [
            'version'       => '6.3',
            'routing_model' => [
                'WELCOME'       => ['ACCOUNT_SETUP'],
                'ACCOUNT_SETUP' => ['SUCCESS'],
                'SUCCESS'       => [],
            ],
            'screens' => [
                self::registerWelcomeScreen(),
                self::registerAccountSetupScreen(),
                self::registerSuccessScreen(),
            ],
        ];
    }

    private static function registerWelcomeScreen(): array
    {
        return [
            'id'       => 'WELCOME',
            'title'    => 'EduBridge',
            'terminal' => false,
            'layout'   => [
                'type'     => 'SingleColumnLayout',
                'children' => [
                    ['type' => 'TextHeading', 'text' => 'Join EduBridge'],
                    ['type' => 'TextBody',    'text' => "Zimbabwe's AI-powered learning platform.\n\nAccess ZIMSEC-aligned courses, study with Chiedza AI, earn certificates and learn via WhatsApp.\n\nCreate your free account in 30 seconds."],
                    ['type' => 'TextCaption', 'text' => 'By continuing you agree to the EduBridge Terms of Service.'],
                    [
                        'type'  => 'Footer',
                        'label' => 'Get Started',
                        'on-click-action' => [
                            'name' => 'navigate',
                            'next' => ['type' => 'screen', 'name' => 'ACCOUNT_SETUP'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function registerAccountSetupScreen(): array
    {
        return [
            'id'       => 'ACCOUNT_SETUP',
            'title'    => 'Create Account',
            'terminal' => false,
            'layout'   => [
                'type'     => 'SingleColumnLayout',
                'children' => [
                    ['type' => 'TextHeading', 'text' => 'Your Details'],
                    [
                        'type'        => 'TextInput',
                        'name'        => 'full_name',
                        'label'       => 'Full Name',
                        'required'    => true,
                        'input-type'  => 'text',
                        'helper-text' => 'As it appears on your ID',
                    ],
                    [
                        'type'       => 'TextInput',
                        'name'       => 'email',
                        'label'      => 'Email Address',
                        'required'   => true,
                        'input-type' => 'email',
                    ],
                    [
                        'type'        => 'RadioButtonsGroup',
                        'name'        => 'role',
                        'label'       => 'I am a...',
                        'required'    => true,
                        'data-source' => [
                            ['id' => 'student', 'title' => 'Student - I want to learn'],
                            ['id' => 'teacher', 'title' => 'Tutor - I want to teach'],
                        ],
                    ],
                    [
                        'type'        => 'Dropdown',
                        'name'        => 'grade_level',
                        'label'       => 'Grade / Level (students)',
                        'required'    => false,
                        'data-source' => [
                            ['id' => 'primary',  'title' => 'Primary School (Grades 1-7)'],
                            ['id' => 'form_1_4', 'title' => 'Form 1-4 (O-Level)'],
                            ['id' => 'form_5_6', 'title' => 'Form 5-6 (A-Level)'],
                            ['id' => 'tertiary', 'title' => 'Tertiary / University'],
                            ['id' => 'adult',    'title' => 'Adult / Professional'],
                        ],
                    ],
                    [
                        'type'  => 'Footer',
                        'label' => 'Create My Account',
                        'on-click-action' => [
                            'name'    => 'navigate',
                            'next'    => ['type' => 'screen', 'name' => 'SUCCESS'],
                            'payload' => [
                                'full_name'   => "\${form.full_name}",
                                'email'       => "\${form.email}",
                                'role'        => "\${form.role}",
                                'grade_level' => "\${form.grade_level}",
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function registerSuccessScreen(): array
    {
        return [
            'id'       => 'SUCCESS',
            'title'    => 'Welcome!',
            'terminal' => true,
            'success'  => true,
            'data'     => [
                'full_name'   => ['type' => 'string', '__example__' => 'John Doe'],
                'email'       => ['type' => 'string', '__example__' => 'john@example.com'],
                'role'        => ['type' => 'string', '__example__' => 'student'],
                'grade_level' => ['type' => 'string', '__example__' => 'form_1_4'],
            ],
            'layout'   => [
                'type'     => 'SingleColumnLayout',
                'children' => [
                    ['type' => 'TextHeading', 'text' => 'Account Created!'],
                    ['type' => 'TextBody',    'text' => "Welcome to EduBridge!\n\nYour account is ready. Visit edu.kmgvitallinks.co.uk to set a password and complete your profile.\n\nOr reply STUDENT HUB to start learning on WhatsApp."],
                    [
                        'type'  => 'Footer',
                        'label' => 'Done',
                        'on-click-action' => [
                            'name'    => 'complete',
                            'payload' => [
                                'full_name'   => "\${data.full_name}",
                                'email'       => "\${data.email}",
                                'role'        => "\${data.role}",
                                'grade_level' => "\${data.grade_level}",
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Flow: chiedza_student  (INJECT-AT-SEND — no endpoint required)
    //
    //  3 screens: DASHBOARD → MAIN_MENU → DONE (terminal)
    //  DASHBOARD shows live stats injected at send time.
    //  MAIN_MENU lets the student pick an action.
    //  DONE returns the menu_action choice.
    // ─────────────────────────────────────────────────────────────────────────

    public static function studentHubFlow(): array
    {
        return [
            'version'       => '6.3',
            'routing_model' => [
                'DASHBOARD' => ['MAIN_MENU'],
                'MAIN_MENU' => ['DONE'],
                'DONE'      => [],
            ],
            'screens' => [
                self::studentDashboardScreen(),
                self::studentMainMenuScreen(),
                self::studentDoneScreen(),
            ],
        ];
    }

    private static function studentDashboardScreen(): array
    {
        return [
            'id'    => 'DASHBOARD',
            'title' => 'My Dashboard',
            'data'  => [
                'student_name'  => ['type' => 'string', '__example__' => 'Takudzwa'],
                'courses_count' => ['type' => 'string', '__example__' => '3 courses enrolled'],
                'xp_points'     => ['type' => 'string', '__example__' => '450 XP'],
                'next_session'  => ['type' => 'string', '__example__' => 'Mathematics — Mon 10am'],
            ],
            'layout' => [
                'type'     => 'SingleColumnLayout',
                'children' => [
                    ['type' => 'TextHeading',    'text' => "Hi, \${data.student_name}!"],
                    ['type' => 'TextSubheading', 'text' => 'Your EduBridge Summary'],
                    ['type' => 'TextBody',       'text' => "Courses: \${data.courses_count}\nXP: \${data.xp_points}\nNext session: \${data.next_session}"],
                    ['type' => 'TextCaption',    'text' => 'Tap below to open your menu.'],
                    [
                        'type'  => 'Footer',
                        'label' => 'Open My Menu',
                        'on-click-action' => [
                            'name' => 'navigate',
                            'next' => ['type' => 'screen', 'name' => 'MAIN_MENU'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function studentMainMenuScreen(): array
    {
        return [
            'id'    => 'MAIN_MENU',
            'title' => 'Student Hub',
            'layout' => [
                'type'     => 'SingleColumnLayout',
                'children' => [
                    ['type' => 'TextHeading', 'text' => 'What would you like to do?'],
                    [
                        'type'        => 'RadioButtonsGroup',
                        'name'        => 'menu_action',
                        'label'       => 'Choose an option',
                        'required'    => true,
                        'data-source' => [
                            ['id' => 'browse',       'title' => 'Browse & Enroll in Courses'],
                            ['id' => 'learn',        'title' => 'Continue Learning'],
                            ['id' => 'ai',           'title' => 'Ask Chiedza AI'],
                            ['id' => 'progress',     'title' => 'My Progress & XP'],
                            ['id' => 'sessions',     'title' => 'Upcoming Live Sessions'],
                            ['id' => 'assignments',  'title' => 'My Assignments'],
                            ['id' => 'certificates', 'title' => 'My Certificates'],
                        ],
                    ],
                    [
                        'type'  => 'Footer',
                        'label' => 'Continue',
                        'on-click-action' => [
                            'name' => 'navigate',
                            'next' => ['type' => 'screen', 'name' => 'DONE'],
                            'payload' => [
                                'menu_action' => "\${form.menu_action}",
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function studentDoneScreen(): array
    {
        return [
            'id'       => 'DONE',
            'title'    => 'EduBridge',
            'terminal' => true,
            'success'  => true,
            'data'     => [
                'menu_action' => ['type' => 'string', '__example__' => 'browse'],
            ],
            'layout' => [
                'type'     => 'SingleColumnLayout',
                'children' => [
                    ['type' => 'TextHeading', 'text' => 'On it!'],
                    ['type' => 'TextBody',    'text' => "We're loading your content now.\n\nReply *MENU* anytime to return here, or type *ASK* followed by a question for Chiedza AI 🤖"],
                    [
                        'type'  => 'Footer',
                        'label' => 'Done',
                        'on-click-action' => [
                            'name'    => 'complete',
                            'payload' => [
                                'menu_action' => "\${data.menu_action}",
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Flow: chiedza_teacher  (INJECT-AT-SEND — no endpoint required)
    //
    //  3 screens: DASHBOARD → MAIN_MENU → DONE (terminal)
    //  DASHBOARD shows live stats. MAIN_MENU offers teacher actions.
    //  DONE returns the menu_action choice to the backend.
    // ─────────────────────────────────────────────────────────────────────────

    public static function teacherHubFlow(): array
    {
        return [
            'version'       => '6.3',
            'routing_model' => [
                'DASHBOARD' => ['MAIN_MENU'],
                'MAIN_MENU' => ['DONE'],
                'DONE'      => [],
            ],
            'screens' => [
                self::teacherDashboardScreen(),
                self::teacherMainMenuScreen(),
                self::teacherDoneScreen(),
            ],
        ];
    }

    private static function teacherDashboardScreen(): array
    {
        return [
            'id'    => 'DASHBOARD',
            'title' => 'Teacher Dashboard',
            'data'  => [
                'teacher_name'   => ['type' => 'string', '__example__' => 'Mr Moyo'],
                'courses_count'  => ['type' => 'string', '__example__' => '4 courses'],
                'sessions_today' => ['type' => 'string', '__example__' => '2 sessions today'],
                'pending_grades' => ['type' => 'string', '__example__' => '7 pending grades'],
            ],
            'layout' => [
                'type'     => 'SingleColumnLayout',
                'children' => [
                    ['type' => 'TextHeading',    'text' => "Good day, \${data.teacher_name}!"],
                    ['type' => 'TextSubheading', 'text' => 'Your Teaching Summary'],
                    ['type' => 'TextBody',       'text' => "📚 \${data.courses_count}\n📅 \${data.sessions_today}\n✅ \${data.pending_grades}"],
                    ['type' => 'TextCaption',    'text' => 'Choose an action from the menu below.'],
                    [
                        'type'  => 'Footer',
                        'label' => 'Open My Menu',
                        'on-click-action' => [
                            'name' => 'navigate',
                            'next' => ['type' => 'screen', 'name' => 'MAIN_MENU'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function teacherMainMenuScreen(): array
    {
        return [
            'id'    => 'MAIN_MENU',
            'title' => 'Teacher Hub',
            'layout' => [
                'type'     => 'SingleColumnLayout',
                'children' => [
                    ['type' => 'TextHeading', 'text' => 'What would you like to do?'],
                    [
                        'type'        => 'RadioButtonsGroup',
                        'name'        => 'menu_action',
                        'label'       => 'Choose an option',
                        'required'    => true,
                        'data-source' => [
                            ['id' => 'announce',  'title' => 'Post Announcement'],
                            ['id' => 'courses',   'title' => 'My Courses'],
                            ['id' => 'grades',    'title' => 'Grade Submissions'],
                            ['id' => 'sessions',  'title' => 'Manage Live Sessions'],
                            ['id' => 'material',  'title' => 'Add Learning Material'],
                            ['id' => 'analytics', 'title' => 'Student Analytics'],
                        ],
                    ],
                    [
                        'type'  => 'Footer',
                        'label' => 'Continue',
                        'on-click-action' => [
                            'name' => 'navigate',
                            'next' => ['type' => 'screen', 'name' => 'DONE'],
                            'payload' => [
                                'menu_action' => "\${form.menu_action}",
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function teacherDoneScreen(): array
    {
        return [
            'id'       => 'DONE',
            'title'    => 'EduBridge',
            'terminal' => true,
            'success'  => true,
            'data'     => [
                'menu_action' => ['type' => 'string', '__example__' => 'grades'],
            ],
            'layout' => [
                'type'     => 'SingleColumnLayout',
                'children' => [
                    ['type' => 'TextHeading', 'text' => 'On it!'],
                    ['type' => 'TextBody',    'text' => "Loading your content now. We'll reply in seconds.\n\nReply *MENU* anytime to return here, or *GRADES* to check pending submissions."],
                    [
                        'type'  => 'Footer',
                        'label' => 'Done',
                        'on-click-action' => [
                            'name'    => 'complete',
                            'payload' => [
                                'menu_action' => "\${data.menu_action}",
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Flow: chiedza_announce  (INJECT-AT-SEND — teacher announcement)
    //
    //  2 screens: ANNOUNCE_FORM → DONE (terminal)
    //  Courses are injected at send time. Returns course_id + message.
    // ─────────────────────────────────────────────────────────────────────────

    public static function announceFlow(): array
    {
        return [
            'version'       => '6.3',
            'routing_model' => [
                'ANNOUNCE_FORM' => ['DONE'],
                'DONE'          => [],
            ],
            'screens' => [
                self::announceFormScreen(),
                self::announceDoneScreen(),
            ],
        ];
    }

    private static function announceFormScreen(): array
    {
        return [
            'id'    => 'ANNOUNCE_FORM',
            'title' => 'Post Announcement',
            'data'  => [
                'courses' => [
                    'type'  => 'array',
                    'items' => [
                        'type'       => 'object',
                        'properties' => [
                            'id'    => ['type' => 'string'],
                            'title' => ['type' => 'string'],
                        ],
                    ],
                    '__example__' => [
                        ['id' => '1', 'title' => 'Mathematics O-Level'],
                    ],
                ],
            ],
            'layout' => [
                'type'     => 'SingleColumnLayout',
                'children' => [
                    ['type' => 'TextHeading', 'text' => 'Post Announcement'],
                    ['type' => 'TextBody',    'text' => 'Your message will be sent to all students enrolled in the selected course.'],
                    [
                        'type'        => 'Dropdown',
                        'name'        => 'course_id',
                        'label'       => 'Course',
                        'required'    => true,
                        'data-source' => "\${data.courses}",
                    ],
                    [
                        'type'        => 'TextArea',
                        'name'        => 'message',
                        'label'       => 'Announcement',
                        'required'    => true,
                        'helper-text' => 'Write your announcement here.',
                    ],
                    [
                        'type'  => 'Footer',
                        'label' => 'Send Announcement',
                        'on-click-action' => [
                            'name' => 'navigate',
                            'next' => ['type' => 'screen', 'name' => 'DONE'],
                            'payload' => [
                                'course_id' => "\${form.course_id}",
                                'message'   => "\${form.message}",
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function announceDoneScreen(): array
    {
        return [
            'id'       => 'DONE',
            'title'    => 'Announcement Sent',
            'terminal' => true,
            'success'  => true,
            'data'     => [
                'course_id' => ['type' => 'string', '__example__' => '1'],
                'message'   => ['type' => 'string', '__example__' => 'Test exam next Monday.'],
            ],
            'layout' => [
                'type'     => 'SingleColumnLayout',
                'children' => [
                    ['type' => 'TextHeading', 'text' => 'Announcement Posted!'],
                    ['type' => 'TextBody',    'text' => "Your announcement is being delivered to all enrolled students.\n\nReply *MENU* to open the Teacher Hub."],
                    [
                        'type'  => 'Footer',
                        'label' => 'Done',
                        'on-click-action' => [
                            'name'    => 'complete',
                            'payload' => [
                                'course_id' => "\${data.course_id}",
                                'message'   => "\${data.message}",
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
