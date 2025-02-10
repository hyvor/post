<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $user_id
 * @property string $name
 */
class Project extends Model
{
    /**
     * @use HasFactory<ProjectFactory>
     */
    use HasFactory;
}
