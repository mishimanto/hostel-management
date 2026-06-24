<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('exit_requests');
        Schema::dropIfExists('seat_change_requests');
        Schema::dropIfExists('booking_requests');
        Schema::dropIfExists('resident_profiles');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('hostel_notifications');
        Schema::dropIfExists('leave_applications');

        if (Schema::hasTable('rooms')) {
            Schema::table('rooms', function (Blueprint $table): void {
                if (! Schema::hasColumn('rooms', 'room_number')) {
                    $table->string('room_number')->nullable()->after('branch_id');
                }
                if (! Schema::hasColumn('rooms', 'monthly_rent')) {
                    $table->decimal('monthly_rent', 10, 2)->default(0)->after('room_number');
                }
                if (! Schema::hasColumn('rooms', 'description')) {
                    $table->text('description')->nullable()->after('monthly_rent');
                }
                if (! Schema::hasColumn('rooms', 'status')) {
                    $table->enum('status', ['available', 'booked', 'maintenance'])->default('available')->after('description');
                }
            });
        }

        if (! Schema::hasTable('room_bookings')) {
            Schema::create('room_bookings', function (Blueprint $table): void {
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
        }

        if (Schema::hasTable('room_bookings')) {
            Schema::table('room_bookings', function (Blueprint $table): void {
                if (! Schema::hasColumn('room_bookings', 'requested_start_date')) {
                    $table->date('requested_start_date')->nullable()->after('monthly_rent');
                }
                if (! Schema::hasColumn('room_bookings', 'requested_end_date')) {
                    $table->date('requested_end_date')->nullable()->after('requested_start_date');
                }
                if (! Schema::hasColumn('room_bookings', 'total_days')) {
                    $table->unsignedSmallInteger('total_days')->default(30)->after('requested_end_date');
                }
                if (! Schema::hasColumn('room_bookings', 'payable_amount')) {
                    $table->decimal('payable_amount', 10, 2)->default(0)->after('total_days');
                }
                if (! Schema::hasColumn('room_bookings', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('payable_amount');
                }
                if (! Schema::hasColumn('room_bookings', 'transaction_id')) {
                    $table->string('transaction_id')->nullable()->after('payment_method');
                }
                if (! Schema::hasColumn('room_bookings', 'payment_details')) {
                    $table->text('payment_details')->nullable()->after('transaction_id');
                }
            });
        }

        if (! Schema::hasTable('room_change_requests')) {
            Schema::create('room_change_requests', function (Blueprint $table): void {
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
        }

        if (Schema::hasTable('room_change_requests') && ! Schema::hasColumn('room_change_requests', 'room_booking_id')) {
            Schema::table('room_change_requests', function (Blueprint $table): void {
                $table->foreignId('room_booking_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table): void {
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
        }

        if (Schema::hasTable('leave_requests') && ! Schema::hasColumn('leave_requests', 'room_booking_id')) {
            Schema::table('leave_requests', function (Blueprint $table): void {
                $table->foreignId('room_booking_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('body');
                $table->enum('type', ['rent', 'room_booking', 'room_change', 'leave', 'announcement', 'general'])->default('general');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table): void {
                if (! Schema::hasColumn('payments', 'room_booking_id')) {
                    $table->foreignId('room_booking_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('payments', 'room_id')) {
                    $table->foreignId('room_id')->nullable()->after('room_booking_id')->constrained()->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('room_change_requests');
        Schema::dropIfExists('room_bookings');
    }
};
