<?php

namespace App\Http\Controllers\DigitalSignature;

use App\Models\Kaprodi;
use Illuminate\Http\Request;
use App\Models\SignatureAuditLog;
use App\Models\SignatureTemplate;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SignatureTemplateController extends Controller
{
    /**
     * Display list template yang tersedia
     */
    public function index()
    {
        try {
            // $user = Auth::user();
            $kaprodi = Kaprodi::find(Auth::id());
            $user = $kaprodi;

            // Filter templates berdasarkan role
            $query = SignatureTemplate::with('kaprodi');

            if ($user->role !== 'admin') {
                // Non-admin hanya bisa lihat template mereka sendiri
                $query->where('kaprodi_id', $user->id);
            }

            $templates = $query->latest()->paginate(10);

            $statistics = [
                'total_templates' => SignatureTemplate::count(),
                'active_templates' => SignatureTemplate::active()->count(),
                'inactive_templates' => SignatureTemplate::where('status', SignatureTemplate::STATUS_INACTIVE)->count(),
                'default_templates' => SignatureTemplate::where('is_default', true)->count(),
            ];

            return view('digital-signature.admin.templates.index', compact('templates', 'statistics'));

        } catch (\Exception $e) {
            Log::error('Template index failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to load templates');
        }
    }

    /**
     * Show form untuk create template baru
     */
    public function create()
    {
        return view('digital-signature.admin.templates.create');
    }

    // private function isValidHexColor($color)
    // {
    //     return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color);
    // }

    /**
     * Store template baru
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'signature_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'logo_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'canvas_width' => 'required|integer|min:400|max:2000',
                'canvas_height' => 'required|integer|min:300|max:1500',
                // 'background_color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
                'background_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
                'kaprodi_name' => 'required|string|max:255',
                'kaprodi_nidn' => 'required|string|max:50',
                'kaprodi_title' => 'required|string|max:255',
                'institution_name' => 'required|string|max:255',
                'is_default' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Upload signature image
            $signatureImagePath = $request->file('signature_image')->store('signature_templates/signatures', 'public');

            // Upload logo image jika ada
            $logoImagePath = null;
            if ($request->hasFile('logo_image')) {
                $logoImagePath = $request->file('logo_image')->store('signature_templates/logos', 'public');
            }

            // Build text config dari form input
            $textConfig = SignatureTemplate::getDefaultTextConfig();
            $textConfig['kaprodi_name']['text'] = $request->kaprodi_name;
            $textConfig['nidn']['text'] = 'NIDN : ' . $request->kaprodi_nidn;
            $textConfig['title']['text'] = $request->kaprodi_title;
            $textConfig['institution']['text'] = $request->institution_name;

            // Create template
            $template = SignatureTemplate::create([
                'name' => $request->name,
                'description' => $request->description,
                'signature_image_path' => $signatureImagePath,
                'logo_path' => $logoImagePath,
                'layout_config' => SignatureTemplate::getDefaultLayoutConfig(),
                'text_config' => $textConfig,
                'kaprodi_id' => Auth::id(),
                'canvas_width' => $request->canvas_width,
                'canvas_height' => $request->canvas_height,
                'background_color' => $request->background_color,
                'style_config' => SignatureTemplate::getDefaultStyleConfig(),
                'is_default' => $request->has('is_default')
            ]);

            // Set sebagai default jika diminta
            if ($request->has('is_default')) {
                $template->setAsDefault();
            }

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'data' => [
                    'template_id' => $template->id,
                    'name' => $template->name,
                    'is_default' => $template->is_default
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Template creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show form untuk edit template
     */
    public function edit($id)
    {
        try {
            $template = SignatureTemplate::findOrFail($id);

            // Check permission
            if ($template->kaprodi_id !== Auth::id() && Auth::user()->role !== 'admin') {
                abort(403, 'Unauthorized to edit this template');
            }

            return view('admin.signature-templates.edit', compact('template'));

        } catch (\Exception $e) {
            Log::error('Template edit form failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to load template for editing');
        }
    }

    /**
     * Update template existing
     */
    public function update(Request $request, $id)
    {
        try {
            $template = SignatureTemplate::findOrFail($id);

            // Check permission
            if ($template->kaprodi_id !== Auth::id() && Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this template'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'signature_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'logo_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'canvas_width' => 'required|integer|min:400|max:2000',
                'canvas_height' => 'required|integer|min:300|max:1500',
                'background_color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
                'kaprodi_name' => 'required|string|max:255',
                'kaprodi_nidn' => 'required|string|max:50',
                'kaprodi_title' => 'required|string|max:255',
                'institution_name' => 'required|string|max:255',
                'is_default' => 'boolean',
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [
                'name' => $request->name,
                'description' => $request->description,
                'canvas_width' => $request->canvas_width,
                'canvas_height' => $request->canvas_height,
                'background_color' => $request->background_color,
                'status' => $request->status
            ];

            // Upload new signature image jika ada
            if ($request->hasFile('signature_image')) {
                // Delete old image
                if ($template->signature_image_path) {
                    Storage::disk('public')->delete($template->signature_image_path);
                }
                $updateData['signature_image_path'] = $request->file('signature_image')->store('signature_templates/signatures', 'public');
            }

            // Upload new logo image jika ada
            if ($request->hasFile('logo_image')) {
                // Delete old logo
                if ($template->logo_path) {
                    Storage::disk('public')->delete($template->logo_path);
                }
                $updateData['logo_path'] = $request->file('logo_image')->store('signature_templates/logos', 'public');
            }

            // Update text config
            $textConfig = $template->text_config;
            $textConfig['kaprodi_name']['text'] = $request->kaprodi_name;
            $textConfig['nidn']['text'] = 'NIDN : ' . $request->kaprodi_nidn;
            $textConfig['title']['text'] = $request->kaprodi_title;
            $textConfig['institution']['text'] = $request->institution_name;
            $updateData['text_config'] = $textConfig;

            $template->update($updateData);

            // Set sebagai default jika diminta
            if ($request->has('is_default') && !$template->is_default) {
                $template->setAsDefault();
            }

            // Log audit
            SignatureAuditLog::create([
                'user_id' => Auth::id(),
                'action' => SignatureAuditLog::ACTION_TEMPLATE_UPDATED,
                'description' => "Template '{$template->name}' has been updated",
                'metadata' => [
                    'template_id' => $template->id,
                    'changes' => array_keys($updateData)
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully',
                'data' => [
                    'template_id' => $template->id,
                    'name' => $template->name,
                    'is_default' => $template->is_default,
                    'status' => $template->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Template update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete template
     */
    public function destroy($id)
    {
        try {
            $template = SignatureTemplate::findOrFail($id);

            // Check permission
            if ($template->kaprodi_id !== Auth::id() && Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this template'
                ], 403);
            }

            // Prevent deleting default template jika hanya satu
            if ($template->is_default) {
                $otherTemplates = SignatureTemplate::where('kaprodi_id', $template->kaprodi_id)
                    ->where('id', '!=', $template->id)
                    ->active()
                    ->count();

                if ($otherTemplates === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete the only active template. Create another template first.'
                    ], 409);
                }
            }

            $templateName = $template->name;

            // Delete associated files
            if ($template->signature_image_path) {
                Storage::disk('public')->delete($template->signature_image_path);
            }
            if ($template->logo_path) {
                Storage::disk('public')->delete($template->logo_path);
            }

            $template->delete();

            // Log audit
            SignatureAuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'template_deleted',
                'description' => "Template '{$templateName}' has been deleted",
                'metadata' => [
                    'template_id' => $id,
                    'template_name' => $templateName
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Template deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload gambar tanda tangan kaprodi
     */
    public function uploadSignatureImage(Request $request, $id)
    {
        try {
            $template = SignatureTemplate::findOrFail($id);

            // Check permission
            if ($template->kaprodi_id !== Auth::id() && Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to upload signature image for this template'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'signature_image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Delete old signature image
            if ($template->signature_image_path) {
                Storage::disk('public')->delete($template->signature_image_path);
            }

            // Upload new signature image
            $signatureImagePath = $request->file('signature_image')->store('signature_templates/signatures', 'public');

            $template->update(['signature_image_path' => $signatureImagePath]);

            return response()->json([
                'success' => true,
                'message' => 'Signature image uploaded successfully',
                'data' => [
                    'signature_image_url' => Storage::url($signatureImagePath),
                    'uploaded_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Signature image upload failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload signature image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set template sebagai default
     */
    public function setDefault($id)
    {
        try {
            $template = SignatureTemplate::findOrFail($id);

            // Check permission
            if ($template->kaprodi_id !== Auth::id() && Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to set this template as default'
                ], 403);
            }

            // Check if template is active
            if ($template->status !== SignatureTemplate::STATUS_ACTIVE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active templates can be set as default'
                ], 400);
            }

            $template->setAsDefault();

            return response()->json([
                'success' => true,
                'message' => 'Template set as default successfully',
                'data' => [
                    'template_id' => $template->id,
                    'name' => $template->name,
                    'is_default' => true
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Set default template failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to set template as default: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clone template untuk kaprodi lain
     */
    public function clone(Request $request, $id)
    {
        try {
            $template = SignatureTemplate::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'kaprodi_id' => 'required|integer|exists:users,id',
                'new_name' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check permission - only admin can clone templates
            if (Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can clone templates'
                ], 403);
            }

            $clonedTemplate = $template->cloneForKaprodi(
                $request->kaprodi_id,
                $request->new_name
            );

            return response()->json([
                'success' => true,
                'message' => 'Template cloned successfully',
                'data' => [
                    'original_template_id' => $template->id,
                    'cloned_template_id' => $clonedTemplate->id,
                    'cloned_template_name' => $clonedTemplate->name,
                    'kaprodi_id' => $clonedTemplate->kaprodi_id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Template cloning failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to clone template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get template details dengan canvas data
     */
    public function show($id)
    {
        try {
            $template = SignatureTemplate::with('kaprodi')->findOrFail($id);

            // Check permission
            if ($template->kaprodi_id !== Auth::id() && Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this template'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'description' => $template->description,
                    'status' => $template->status,
                    'is_default' => $template->is_default,
                    'canvas_width' => $template->canvas_width,
                    'canvas_height' => $template->canvas_height,
                    'background_color' => $template->background_color,
                    'signature_image_url' => $template->signature_image_path ? Storage::url($template->signature_image_path) : null,
                    'logo_url' => $template->logo_path ? Storage::url($template->logo_path) : null,
                    'layout_config' => $template->layout_config,
                    'text_config' => $template->text_config,
                    'style_config' => $template->style_config,
                    'kaprodi' => [
                        'name' => $template->kaprodi->name,
                        'email' => $template->kaprodi->email
                    ],
                    'statistics' => $template->getStatistics(),
                    'created_at' => $template->created_at->toISOString(),
                    'updated_at' => $template->updated_at->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Template details retrieval failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve template details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active templates untuk dropdown selection
     */
    public function getActiveTemplates()
    {
        try {
            $user = Auth::user();

            $query = SignatureTemplate::active();

            if ($user->role !== 'admin') {
                $query->where('kaprodi_id', $user->id);
            }

            $templates = $query->select('id', 'name', 'description', 'is_default', 'usage_count')
                ->get()
                ->map(function ($template) {
                    return [
                        'id' => $template->id,
                        'name' => $template->name,
                        'description' => $template->description,
                        'is_default' => $template->is_default,
                        'usage_count' => $template->usage_count,
                        'label' => $template->name . ($template->is_default ? ' (Default)' : '')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $templates
            ]);

        } catch (\Exception $e) {
            Log::error('Active templates retrieval failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active templates: ' . $e->getMessage()
            ], 500);
        }
    }
}
