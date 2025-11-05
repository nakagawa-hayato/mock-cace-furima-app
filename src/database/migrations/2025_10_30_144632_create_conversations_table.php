<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE:
     * - This migration creates the conversations table with is_completed and is_rated
     *   as non-nullable booleans with default false, so migrate:fresh will produce
     *   rows where those columns are always 0/1, avoiding NULL-related logic bugs.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // item / participants
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('buyer_id')->nullable()->constrained('users')->onDelete('set null');

            // 最終メッセージ日時（任意）
            $table->timestamp('last_message_at')->nullable();

            // 取引完了フラグ（boolean）と完了日時
            // 明示的に NOT NULL にし、デフォルト false（0）とする
            $table->boolean('is_completed')->default(false)->nullable(false);
            $table->timestamp('completed_at')->nullable();

            // 評価済みフラグ（NOT NULL, default false）
            $table->boolean('is_rated')->default(false)->nullable(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversations');
    }
}
