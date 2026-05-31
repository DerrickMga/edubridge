<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Versioned policy library (Code of Conduct, IP Assignment, Confidentiality, etc.)
        Schema::create('teacher_policies', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80);
            $table->string('title', 200);
            $table->string('category', 40); // code_of_conduct|ip|confidentiality|acceptable_use|anti_harassment|payments|safeguarding|termination|data_protection|general
            $table->unsignedInteger('version')->default(1);
            $table->longText('body'); // markdown
            $table->boolean('is_active')->default(true);
            $table->timestamp('effective_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['slug', 'version']);
            $table->index(['category', 'is_active']);
        });

        // Per-teacher acknowledgements (a teacher must re-ack when a new version goes live)
        Schema::create('teacher_policy_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('policy_id')->constrained('teacher_policies')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->timestamp('acknowledged_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
            $table->unique(['teacher_id', 'policy_id', 'version'], 'tpa_unique');
            $table->index('teacher_id');
        });

        // Master contract per teacher with snapshot of accepted policy versions
        Schema::create('teacher_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('status', 20)->default('pending'); // pending|signed|terminated|expired|superseded
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('effective_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->decimal('rate_usd', 8, 2)->nullable();
            $table->string('payment_terms', 80)->nullable(); // e.g. "Monthly, net 14"
            $table->unsignedSmallInteger('term_months')->nullable();
            $table->string('exclusivity', 16)->default('non_exclusive'); // exclusive|non_exclusive
            $table->json('terms_snapshot')->nullable(); // [{policy_id,slug,title,version}, ...]
            $table->longText('addendum')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signed_name', 200)->nullable();
            $table->string('signed_ip', 45)->nullable();
            $table->string('signed_user_agent', 500)->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->string('terminated_reason', 500)->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['teacher_id', 'status']);
        });

        // Contingency matrix — admin-defined risk events with response playbooks
        Schema::create('policy_risk_events', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique(); // e.g. NO_SHOW, ABUSIVE_CONDUCT
            $table->string('title', 200);
            $table->string('category', 40); // conduct|attendance|ip|safeguarding|payment|legal|technical
            $table->string('severity', 16); // low|medium|high|critical
            $table->text('trigger')->nullable(); // when does this fire
            $table->longText('response_playbook'); // markdown steps
            $table->foreignId('policy_id')->nullable()->constrained('teacher_policies')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['severity', 'category']);
        });

        // Logged incidents against a teacher (an instance of a risk event)
        Schema::create('policy_risk_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_event_id')->constrained('policy_risk_events')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 16)->default('open'); // open|investigating|resolved|escalated|dismissed
            $table->string('severity_override', 16)->nullable();
            $table->text('summary');
            $table->longText('resolution_notes')->nullable();
            $table->json('evidence')->nullable(); // array of strings (urls/file ids)
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['teacher_id', 'status']);
            $table->index('risk_event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_risk_incidents');
        Schema::dropIfExists('policy_risk_events');
        Schema::dropIfExists('teacher_contracts');
        Schema::dropIfExists('teacher_policy_acknowledgements');
        Schema::dropIfExists('teacher_policies');
    }
};
