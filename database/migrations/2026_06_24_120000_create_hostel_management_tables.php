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
            $table->string('room_number');
            $table->decimal('monthly_rent', 10, 2);
            $table->text('description')->nullable();
            $table->enum('status', ['available', 'booked', 'maintenance'])->default('available');
            $table->timestamps();
            $table->unique(['branch_id', 'room_number']);
        });

        Schema::create('room_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->decimal('monthly_rent', 10, 2);
            $table->date('requested_start_date');
            $table->date('requested_end_date');
            $table->unsignedSmallInteger('total_days');
            $table->decimal('payable_amount', 10, 2);
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->text('payment_details')->nullable();
            $table->date('paid_until')->nullable();
            $table->date('started_at')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
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

        Schema::create('room_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('current_room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('requested_room_id')->constrained('rooms')->cascadeOnDelete();
            $table->date('change_date');
            $table->decimal('old_monthly_rent', 10, 2);
            $table->decimal('new_monthly_rent', 10, 2);
            $table->unsignedSmallInteger('remaining_paid_days')->default(0);
            $table->decimal('additional_payable', 10, 2)->default(0);
            $table->unsignedSmallInteger('extra_days')->default(0);
            $table->date('new_paid_until')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_booking_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->enum('type', ['rent', 'room_booking', 'room_change', 'leave', 'announcement', 'general'])->default('general');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('room_change_requests');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('room_bookings');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('branches');
    }
};
