<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'credit_balance')) {
                $table->decimal('credit_balance', 12, 2)->default(0)->after('newsletter');
            }
            if (! Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code', 32)->nullable()->unique()->after('credit_balance');
            }
            if (! Schema::hasColumn('users', 'referred_by')) {
                $table->foreignId('referred_by')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            }
        });

        if (! Schema::hasTable('credit_transactions')) {
            Schema::create('credit_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('type', 40); // topup, purchase, referral, admin_adjust, refund
                $table->string('description')->nullable();
                $table->string('reference')->nullable()->index();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('credit_packages')) {
            Schema::create('credit_packages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('credits', 12, 2);
                $table->decimal('price', 12, 2);
                $table->string('currency', 3)->default('USD');
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('guest_email')->nullable();
                $table->string('guest_name')->nullable();
                $table->string('subject');
                $table->string('status', 20)->default('open'); // open, pending, closed
                $table->string('priority', 20)->default('normal');
                $table->timestamp('last_reply_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('support_messages')) {
            Schema::create('support_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->boolean('is_staff')->default(false);
                $table->text('body');
                $table->timestamps();
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('stripe_session_id');
            }
            if (! Schema::hasColumn('orders', 'paid_with_credits')) {
                $table->boolean('paid_with_credits')->default(false)->after('payment_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('credit_packages');
        Schema::dropIfExists('credit_transactions');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'paid_with_credits')) {
                $table->dropColumn('paid_with_credits');
            }
            if (Schema::hasColumn('orders', 'payment_reference')) {
                $table->dropColumn('payment_reference');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'referred_by')) {
                $table->dropConstrainedForeignId('referred_by');
            }
            if (Schema::hasColumn('users', 'referral_code')) {
                $table->dropColumn('referral_code');
            }
            if (Schema::hasColumn('users', 'credit_balance')) {
                $table->dropColumn('credit_balance');
            }
        });
    }
};
