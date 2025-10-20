<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\SignatureAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class SignatureTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'signature_image_path',
        'logo_path',
        'layout_config',
        'text_config',
        'kaprodi_id',
        'status',
        'is_default',
        'canvas_width',
        'canvas_height',
        'background_color',
        'style_config',
        'usage_count',
        'last_used_at'
    ];

    protected $casts = [
        'layout_config' => 'array',
        'text_config' => 'array',
        'style_config' => 'array',
        'is_default' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Set default layout config jika kosong
            if (empty($model->layout_config)) {
                $model->layout_config = self::getDefaultLayoutConfig();
            }

            // Set default text config jika kosong
            if (empty($model->text_config)) {
                $model->text_config = self::getDefaultTextConfig();
            }

            // Set default style config
            if (empty($model->style_config)) {
                $model->style_config = self::getDefaultStyleConfig();
            }
        });

        static::created(function ($model) {
            // Log audit untuk template creation
            SignatureAuditLog::create([
                'user_id' => Auth::id() ?? $model->kaprodi_id,
                'action' => 'template_created',
                'description' => "Signature template '{$model->name}' has been created",
                'metadata' => [
                    'template_id' => $model->id,
                    'template_name' => $model->name
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now()
            ]);
        });
    }

    /**
     * Relasi ke User (Kaprodi)
     */
    public function kaprodi()
    {
        return $this->belongsTo(Kaprodi::class, 'kaprodi_id');
    }

    /**
     * Get default layout configuration
     */
    public static function getDefaultLayoutConfig()
    {
        return [
            'canvas_width' => 800,
            'canvas_height' => 600,
            'barcode_position' => [
                'x' => 50,
                'y' => 50,
                'width' => 150,
                'height' => 150
            ],
            'signature_position' => [
                'x' => 220,
                'y' => 50,
                'width' => 200,
                'height' => 100
            ],
            'text_position' => [
                'x' => 220,
                'y' => 160,
                'width' => 300,
                'height' => 120
            ],
            'logo_position' => [
                'x' => 550,
                'y' => 50,
                'width' => 120,
                'height' => 120
            ],
            'document_info_position' => [
                'x' => 50,
                'y' => 220,
                'width' => 700,
                'height' => 200
            ]
        ];
    }

    /**
     * Get default text configuration
     */
    public static function getDefaultTextConfig()
    {
        return [
            'kaprodi_name' => [
                'text' => 'Yani Sugiyani, MM., M.Kom',
                'font_size' => 14,
                'font_weight' => 'bold',
                'color' => '#000000',
                'font_family' => 'Arial, sans-serif'
            ],
            'nidn' => [
                'text' => 'NIDN : 0419038004',
                'font_size' => 12,
                'font_weight' => 'normal',
                'color' => '#000000',
                'font_family' => 'Arial, sans-serif'
            ],
            'title' => [
                'text' => 'Prodi Teknik Informatika',
                'font_size' => 12,
                'font_weight' => 'normal',
                'color' => '#000000',
                'font_family' => 'Arial, sans-serif'
            ],
            'institution' => [
                'text' => 'Fakultas Teknik - Universitas Muhammadiyah Tangerang',
                'font_size' => 11,
                'font_weight' => 'normal',
                'color' => '#666666',
                'font_family' => 'Arial, sans-serif'
            ],
            'location_date' => [
                'text' => 'Tangerang, {date}',
                'font_size' => 12,
                'font_weight' => 'normal',
                'color' => '#000000',
                'font_family' => 'Arial, sans-serif'
            ],
            'document_info' => [
                'show_document_name' => true,
                'show_document_number' => true,
                'show_user_name' => true,
                'show_user_nim' => true,
                'font_size' => 11,
                'color' => '#333333',
                'font_family' => 'Arial, sans-serif'
            ]
        ];
    }

    /**
     * Get default style configuration
     */
    public static function getDefaultStyleConfig()
    {
        return [
            'border' => [
                'show' => true,
                'color' => '#cccccc',
                'width' => 1,
                'style' => 'solid'
            ],
            'background' => [
                'color' => '#ffffff',
                'opacity' => 1
            ],
            'shadow' => [
                'show' => false,
                'color' => '#000000',
                'blur' => 5,
                'offset_x' => 2,
                'offset_y' => 2,
                'opacity' => 0.3
            ],
            'watermark' => [
                'show' => false,
                'text' => 'VERIFIED',
                'color' => '#f0f0f0',
                'font_size' => 48,
                'opacity' => 0.1,
                'rotation' => -45
            ]
        ];
    }

    /**
     * Get processed text config dengan dynamic values
     */
    public function getProcessedTextConfig($documentSignature = null)
    {
        $config = $this->text_config;

        // Replace dynamic placeholders
        if (isset($config['location_date']['text'])) {
            $config['location_date']['text'] = str_replace(
                '{date}',
                now()->locale('id')->translatedFormat('d F Y'),
                $config['location_date']['text']
            );
        }

        // Add document-specific info jika ada
        if ($documentSignature && isset($config['document_info'])) {
            $approvalRequest = $documentSignature->approvalRequest;
            $config['document_info']['data'] = [
                'document_name' => $approvalRequest->document_name,
                'document_number' => $approvalRequest->full_document_number,
                'user_name' => $approvalRequest->user->name,
                'user_nim' => $approvalRequest->user->NIM ?? 'N/A',
                'submission_date' => $approvalRequest->created_at->format('d F Y')
            ];
        }

        return $config;
    }

    /**
     * Scope untuk template aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Get template untuk kaprodi tertentu
     */
    public function scopeForKaprodi($query, $kaprodiId)
    {
        return $query->where('kaprodi_id', $kaprodiId);
    }

    /**
     * Get template default
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Check apakah template ini adalah default
     */
    public function isDefault()
    {
        return $this->is_default;
    }

    /**
     * Set sebagai template default
     */
    public function setAsDefault()
    {
        // Reset semua template default untuk kaprodi yang sama
        self::where('kaprodi_id', $this->kaprodi_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set template ini sebagai default
        $this->update(['is_default' => true]);

        // Log audit
        SignatureAuditLog::create([
            'user_id' => Auth::id() ?? $this->kaprodi_id,
            'action' => 'template_set_default',
            'description' => "Template '{$this->name}' has been set as default",
            'metadata' => [
                'template_id' => $this->id,
                'kaprodi_id' => $this->kaprodi_id
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now()
        ]);
    }

    /**
     * Update usage count
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Generate signature canvas data
     */
    public function generateCanvasData($documentSignature)
    {
        $canvasData = [
            'template_id' => $this->id,
            'template_name' => $this->name,
            'canvas_dimensions' => [
                'width' => (int) $this->canvas_width,
                'height' => (int) $this->canvas_height
            ],
            'background_color' => $this->background_color,
            'layout_config' => $this->layout_config,
            'text_config' => $this->getProcessedTextConfig($documentSignature),
            'style_config' => $this->style_config,
            'assets' => [
                'signature_image_url' => $this->signature_image_path ?
                    Storage::url($this->signature_image_path) : null,
                'logo_url' => $this->logo_path ?
                    Storage::url($this->logo_path) : null,
            ],
            'qr_code_data' => $documentSignature->verification_url,
            'document_info' => [
                'name' => $documentSignature->approvalRequest->document_name,
                'nomor' => $documentSignature->approvalRequest->full_document_number,
                'user_name' => $documentSignature->approvalRequest->user->name,
                'user_nim' => $documentSignature->approvalRequest->user->NIM ?? 'N/A',
                'created_at' => $documentSignature->approvalRequest->created_at->format('d F Y')
            ],
            'signature_metadata' => [
                'algorithm' => $documentSignature->digitalSignature->algorithm,
                'key_length' => $documentSignature->digitalSignature->key_length,
                'signature_id' => $documentSignature->digitalSignature->signature_id,
                'verification_token' => $documentSignature->verification_token
            ]
        ];

        // Increment usage count
        $this->incrementUsage();

        return $canvasData;
    }

    /**
     * Validate template configuration
     */
    public function validateConfiguration()
    {
        $errors = [];

        // Validate signature image
        if (!$this->signature_image_path || !Storage::disk('public')->exists($this->signature_image_path)) {
            $errors[] = 'Signature image not found or invalid';
        }

        // Validate canvas dimensions
        if (!is_numeric($this->canvas_width) || $this->canvas_width < 400) {
            $errors[] = 'Canvas width must be at least 400px';
        }
        if (!is_numeric($this->canvas_height) || $this->canvas_height < 300) {
            $errors[] = 'Canvas height must be at least 300px';
        }

        // Validate layout config
        $requiredPositions = ['barcode_position', 'signature_position', 'text_position'];
        foreach ($requiredPositions as $position) {
            if (!isset($this->layout_config[$position])) {
                $errors[] = "Missing required position configuration: {$position}";
            }
        }

        return $errors;
    }

    /**
     * Clone template untuk kaprodi lain
     */
    public function cloneForKaprodi($newKaprodiId, $newName = null)
    {
        $clonedTemplate = $this->replicate();
        $clonedTemplate->kaprodi_id = $newKaprodiId;
        $clonedTemplate->name = $newName ?? ($this->name . ' (Copy)');
        $clonedTemplate->is_default = false;
        $clonedTemplate->usage_count = 0;
        $clonedTemplate->last_used_at = null;
        $clonedTemplate->save();

        // Copy signature image jika ada
        if ($this->signature_image_path) {
            $originalPath = $this->signature_image_path;
            $newPath = 'signature_templates/' . $clonedTemplate->id . '_' . basename($originalPath);

            if (Storage::disk('public')->exists($originalPath)) {
                Storage::disk('public')->copy($originalPath, $newPath);
                $clonedTemplate->signature_image_path = $newPath;
                $clonedTemplate->save();
            }
        }

        return $clonedTemplate;
    }

    /**
     * Get template statistics
     */
    public function getStatistics()
    {
        return [
            'usage_count' => $this->usage_count,
            'last_used' => $this->last_used_at ? $this->last_used_at->diffForHumans() : 'Never used',
            'created_date' => $this->created_at->format('d F Y'),
            'is_active' => $this->status === self::STATUS_ACTIVE,
            'is_default' => $this->is_default,
            'configuration_valid' => empty($this->validateConfiguration())
        ];
    }
}
