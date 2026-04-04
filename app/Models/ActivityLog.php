<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a human-readable label for the action.
     */
    public function getActionLabel(): string
    {
        $labels = [
            'user_login' => 'User Login',
            'user_logout' => 'User Logout',
            'user_activated' => 'User Activated',
            'user_deactivated' => 'User Deactivated',
            'backup_created' => 'Backup Created',
            'backup_failed' => 'Backup Failed',
            'backup_downloaded' => 'Backup Downloaded',
            'backup_deleted' => 'Backup Deleted',
            'admin_promoted' => 'Admin Promoted',
            'admin_revoked' => 'Admin Revoked',
            'transaction_created' => 'Transaction Created',
            'transaction_updated' => 'Transaction Updated',
            'transaction_deleted' => 'Transaction Deleted',
            'settings_updated' => 'Settings Updated',
        ];

        return $labels[$this->action] ?? ucwords(str_replace('_', ' ', $this->action));
    }

    /**
     * Get a specific value from the metadata array.
     */
    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->metadata, $key, $default);
    }
}
