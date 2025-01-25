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

        CREATE TYPE issues_status AS ENUM ('draft', 'scheduled', 'sending', 'failed', 'sent');

        CREATE TABLE newsletter_issues (
            id BIGSERIAL PRIMARY KEY,
            uuid VARCHAR(255) UNIQUE NOT NULL,
            list_id BIGINT NOT NULL,
            subject VARCHAR(255) NULL,
            from_name VARCHAR(255) NULL,
            from_email VARCHAR(255) NOT NULL,
            reply_to_email VARCHAR(255) NULL,
            content TEXT NULL,
            status issues_status,
            html TEXT NULL,
            text TEXT NULL,
            scheduled_at TIMESTAMP NULL,
            sending_at TIMESTAMP NULL,
            failed_at TIMESTAMP NULL,
            sent_at TIMESTAMP NULL,
            error_private TEXT NULL,
            batch_id VARCHAR(255) UNIQUE NULL,
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
