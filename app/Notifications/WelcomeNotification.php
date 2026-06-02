<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly User $user) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isTeacher = $this->user->role === 'teacher';
        $name      = $this->user->name;

        $mail = (new MailMessage)
            ->subject($isTeacher
                ? "Welcome to EduBridge — Your Teaching Hub is Ready!"
                : "Welcome to EduBridge — Start Your Learning Journey!")
            ->greeting("Hi {$name},");

        if ($isTeacher) {
            $mail
                ->line("You've joined **EduBridge** as a **Tutor** — welcome aboard!")
                ->line("Here's a quick overview of what you can do:")
                ->line("**📖 Create Courses** — Build structured courses with lessons, quizzes, assignments and resources. Use our AI quiz generator to save time.")
                ->line("**🎥 Live Sessions** — Schedule interactive live classes with a Zoom/Meet link. Students are notified automatically and sessions are recorded.")
                ->line("**👥 Co-Teaching** — Browse the course catalogue and join existing courses as a co-teacher to collaborate with other tutors.")
                ->line("**📊 Session AI Reports** — After each live session, an AI generates a detailed report covering attendance, topics taught and follow-up recommendations.")
                ->line("**💬 WhatsApp Integration** — Students can message you via WhatsApp. All messages route to your conversation inbox in EduBridge.")
                ->action('Complete Your Profile & Get Started', url('/onboarding/1'))
                ->line("---")
                ->line("Your first step is to complete your **onboarding walkthrough** — it takes about 2 minutes and ensures your profile looks great to students.")
                ->line("If you need help at any point, just reply to this email.")
                ->salutation("Welcome to the team,\n— The EduBridge Team");
        } else {
            $mail
                ->line("You've joined **EduBridge** as a **Student** — your learning adventure starts now!")
                ->line("Here's everything that's waiting for you:")
                ->line("**📚 Hundreds of Courses** — Browse expert-taught courses across maths, sciences, languages, arts and more. Filter by subject, grade level or tutor.")
                ->line("**🤖 Chiedza — Your AI Companion** — Stuck on a concept? Ask Chiedza any question 24/7 and get instant, personalised explanations and practice questions.")
                ->line("**📅 Live Sessions** — Join interactive live classes with real teachers. Ask questions in real time and catch up with recordings afterwards.")
                ->line("**🏆 Achievements & Certificates** — Earn XP, unlock badges and collect certificates as you complete courses. Compete on the global leaderboard.")
                ->line("**📓 Smart Notebook** — Take notes while watching lessons. Your notes are tied to each lesson so they're always easy to find.")
                ->line("**👨‍👩‍👧 Refer & Earn** — Invite friends and earn rewards every time they sign up using your referral link.")
                ->action('Start Your Onboarding Walkthrough', url('/onboarding/1'))
                ->line("---")
                ->line("Your first step is to complete your **quick onboarding walkthrough** — it personalises your experience and helps us recommend the right courses for you.")
                ->line("Questions? Just reply to this email — we're here to help.")
                ->salutation("Happy learning,\n— The EduBridge Team");
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'welcome',
            'message' => "Welcome to EduBridge, {$this->user->name}! Complete your profile to get started.",
            'url'     => '/onboarding/1',
        ];
    }
}

