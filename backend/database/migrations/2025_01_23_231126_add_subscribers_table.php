<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $query = <<<SQL

        CREATE TYPE subscriber_status AS ENUM ('subscribed', 'unsubscribed', 'pending');
        CREATE TYPE subscriber_source AS ENUM ('console', 'form', 'import', 'auto_subscribe');

        CREATE TABLE subscribers (
            id BIGSERIAL PRIMARY KEY,
            list_id BIGINT NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            status subscriber_status DEFAULT 'pending',
            subscribed_at TIMESTAMP NULL,
            unsubscribed_at TIMESTAMP NULL,
            source subscriber_source DEFAULT 'form',
            soure_id TEXT,
            subscribe_ip VARCHAR(255) NULL,
            unsubscribe_reason TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        SQL;

        DB::unprepared($query);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
