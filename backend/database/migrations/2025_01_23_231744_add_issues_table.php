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

        DROP TYPE IF EXISTS issues_status;

        CREATE TYPE issues_status AS ENUM ('draft', 'scheduled', 'sending', 'failed', 'sent');

        CREATE TABLE issues (
            id BIGSERIAL PRIMARY KEY,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            uuid VARCHAR(255) UNIQUE NOT NULL,
            list_id BIGINT NOT NULL references lists(id),
            subject VARCHAR(255),
            from_name VARCHAR(255),
            from_email VARCHAR(255) NOT NULL,
            reply_to_email VARCHAR(255),
            content TEXT NULL,
            status issues_status,
            html TEXT NULL,
            text TEXT NULL,
            scheduled_at TIMESTAMP,
            sending_at TIMESTAMP,
            failed_at TIMESTAMP,
            sent_at TIMESTAMP,
            error_private TEXT,
            batch_id VARCHAR(255) UNIQUE
        );

        SQL;

        DB::unprepared($query);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
