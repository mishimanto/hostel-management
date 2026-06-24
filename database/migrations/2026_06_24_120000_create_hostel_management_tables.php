<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('phone')->nullable();
            $table->text('address');
            $table->unsignedTinyInteger('rent_due_day')->default(5);
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('room_no');
            $table->unsignedTinyInteger('capacity');
            $table->string('floor')->nullable();
            $table->timestamps();
            $table->unique(['branch_id', 'room_no']);
        });

        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('monthly_rent', 10, 2);
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->unique(['room_id', 'label']);
        });

        Schema::create('resident_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seat_id')->constrained()->cascadeOnDelete();
            $table->date('joined_at');
            $table->decimal('balance', 10, 2)->default(0);
            $table->decimal('deposit_paid', 10, 2)->default(0);
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->enum('status', ['active', 'notice', 'exited'])->default('active');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seat_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_no')->unique();
            $table->date('billing_month');
            $table->date('due_date');
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('adjustment_amount', 10, 2)->default(0);
            $table->enum('status', ['due', 'partial', 'paid'])->default('due');
            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('seat_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('current_seat_id')->constrained('seats')->cascadeOnDelete();
            $table->foreignId('requested_seat_id')->constrained('seats')->cascadeOnDelete();
            $table->enum('type', ['same_branch', 'different_branch']);
            $table->decimal('current_rent', 10, 2);
            $table->decimal('requested_rent', 10, 2);
            $table->decimal('balance_before', 10, 2)->default(0);
            $table->decimal('payable_amount', 10, 2)->default(0);
            $table->decimal('credit_to_next_rent', 10, 2)->default(0);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('exit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('requested_exit_date');
            $table->unsignedSmallInteger('notice_days');
            $table->decimal('rent_due', 10, 2)->default(0);
            $table->decimal('deposit_adjustment', 10, 2)->default(0);
            $table->decimal('balance_adjustment', 10, 2)->default(0);
            $table->decimal('final_payable', 10, 2)->default(0);
            $table->decimal('final_refundable', 10, 2)->default(0);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'settled'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('hostel_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->enum('type', ['rent', 'seat_change', 'leave', 'exit', 'announcement', 'general'])->default('general');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_notifications');
        Schema::dropIfExists('exit_requests');
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('seat_change_requests');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('resident_profiles');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('branches');
    }
};
