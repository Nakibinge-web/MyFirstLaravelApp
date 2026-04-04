<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Backup extends Model
{
    protected $fillable = [
        'filename',
        'path',
        'size',
        'description',
        'created_by',
        'status',
    ];

    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created the backup.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the backup file size in human-readable format.
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->size;
        
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        
        $size = $bytes / pow(1024, $power);
        
        return round($size, 2) . ' ' . $units[$power];
    }

    /**
     * Check if the backup file exists on disk.
     */
    public function exists(): bool
    {
        return file_exists($this->path);
    }

    /**
     * Delete the backup file and database record.
     */
    public function delete(): ?bool
    {
        // Delete the physical file if it exists
        if ($this->exists()) {
            @unlink($this->path);
        }

        // Delete the database record
        return parent::delete();
    }
}
