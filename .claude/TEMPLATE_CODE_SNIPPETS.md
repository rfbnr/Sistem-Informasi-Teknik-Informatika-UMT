# 📝 SIGNATURE TEMPLATE - CODE SNIPPETS REFERENCE

Quick copy-paste snippets untuk implementasi features.

---

## 1. CANVAS PREVIEW INTEGRATION

### Include in Blade View:
```blade
@include('digital-signature.admin.templates.partials.canvas-preview', [
    'canvasId' => 'yourCanvasId',
    'containerId' => 'yourContainerId',
    'showZoom' => true,
    'height' => '400px'
])
```

### Include Renderer Script:
```blade
@push('scripts')
@include('digital-signature.admin.templates.partials.canvas-renderer')
<script>
// Your custom JavaScript here
</script>
@endpush
```

### Call Render Function:
```javascript
const config = getTemplateConfigFromForm({
    canvas_width: $('#canvas_width').val(),
    canvas_height: $('#canvas_height').val(),
    background_color: $('#background_color').val(),
    signature_image_url: signatureImagePreviewUrl,
    logo_url: logoImagePreviewUrl,
    kaprodi_name: $('#kaprodi_name').val(),
    kaprodi_nidn: $('#kaprodi_nidn').val(),
    kaprodi_title: $('#kaprodi_title').val(),
    institution_name: $('#institution_name').val()
});

renderTemplateCanvas('yourCanvasId', config);
```

---

## 2. CONTROLLER - SEARCH & FILTER

```php
public function index(Request $request)
{
    try {
        $query = SignatureTemplate::with('kaprodi');

        // Filter by kaprodi (non-admin)
        if ($user->role !== 'admin') {
            $query->where('kaprodi_id', $user->id);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category (if implemented)
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Sort
        switch ($request->get('sort', 'latest')) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'usage':
                $query->orderBy('usage_count', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default: // latest
                $query->latest();
        }

        $templates = $query->paginate(15)->withQueryString();

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
```

---

## 3. VIEW - SEARCH & FILTER UI

```blade
<!-- Search & Filter Section -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-text bg-white">
                <i class="fas fa-search"></i>
            </span>
            <input type="text"
                   class="form-control"
                   id="searchInput"
                   placeholder="Search templates by name..."
                   value="{{ request('search') }}">
        </div>
    </div>
    <div class="col-md-2">
        <select class="form-select" id="statusFilter">
            <option value="">All Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
    <div class="col-md-2">
        <select class="form-select" id="sortBy">
            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest First</option>
            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
            <option value="usage" {{ request('sort') == 'usage' ? 'selected' : '' }}>Most Used</option>
        </select>
    </div>
    <div class="col-md-2">
        <button type="button" class="btn btn-primary w-100" onclick="applyFilters()">
            <i class="fas fa-filter me-1"></i> Apply
        </button>
    </div>
    <div class="col-md-2">
        <button type="button" class="btn btn-outline-secondary w-100" onclick="resetFilters()">
            <i class="fas fa-redo me-1"></i> Reset
        </button>
    </div>
</div>

<!-- JavaScript -->
@push('scripts')
<script>
function applyFilters() {
    const search = $('#searchInput').val();
    const status = $('#statusFilter').val();
    const sort = $('#sortBy').val();

    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (sort) params.append('sort', sort);

    window.location.href = `/admin/signature/templates?${params.toString()}`;
}

function resetFilters() {
    window.location.href = '/admin/signature/templates';
}

// Enter key to search
$('#searchInput').on('keypress', function(e) {
    if (e.which === 13) {
        applyFilters();
    }
});
</script>
@endpush
```

---

## 4. TEXT STYLING UI

```blade
<!-- Text Styling Configuration -->
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="fas fa-font me-2"></i>
            Text Styling Configuration
        </h5>
    </div>
    <div class="card-body">
        <!-- Kaprodi Name Styling -->
        <div class="mb-4">
            <h6 class="text-primary mb-3">Kaprodi Name Style</h6>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Font Family</label>
                    <select class="form-select" id="kaprodi_name_family" onchange="updateCanvasPreview()">
                        <option value="Arial, sans-serif">Arial</option>
                        <option value="Times New Roman, serif">Times New Roman</option>
                        <option value="Courier New, monospace">Courier New</option>
                        <option value="Georgia, serif">Georgia</option>
                        <option value="Verdana, sans-serif">Verdana</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Font Size (px)</label>
                    <input type="number"
                           class="form-control"
                           id="kaprodi_name_size"
                           value="14"
                           min="8"
                           max="48"
                           onchange="updateCanvasPreview()">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Font Weight</label>
                    <select class="form-select" id="kaprodi_name_weight" onchange="updateCanvasPreview()">
                        <option value="normal">Normal (400)</option>
                        <option value="600">Semi-Bold (600)</option>
                        <option value="bold">Bold (700)</option>
                        <option value="800">Extra Bold (800)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Color</label>
                    <div class="input-group">
                        <input type="color"
                               class="form-control form-control-color"
                               id="kaprodi_name_color"
                               value="#000000"
                               onchange="updateCanvasPreview()">
                        <input type="text"
                               class="form-control"
                               id="kaprodi_name_color_hex"
                               value="#000000"
                               pattern="^#([A-Fa-f0-9]{6})$">
                    </div>
                </div>
            </div>
        </div>

        <!-- Repeat for NIDN, Title, Institution, Location/Date -->
        <!-- Use same structure, just change IDs -->
    </div>
</div>

<!-- JavaScript for color sync -->
<script>
$('#kaprodi_name_color').on('input', function() {
    $('#kaprodi_name_color_hex').val($(this).val());
    updateCanvasPreview();
});

$('#kaprodi_name_color_hex').on('input', function() {
    const color = $(this).val();
    if (/^#[0-9A-F]{6}$/i.test(color)) {
        $('#kaprodi_name_color').val(color);
        updateCanvasPreview();
    }
});
</script>
```

---

## 5. STYLE CONFIG UI

```blade
<!-- Style Configuration -->
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">
            <i class="fas fa-paint-brush me-2"></i>
            Style Configuration
        </h5>
    </div>
    <div class="card-body">
        <!-- Border Controls -->
        <div class="mb-4 p-3 border rounded">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Border</h6>
                <div class="form-check form-switch">
                    <input type="checkbox"
                           class="form-check-input"
                           id="border_show"
                           checked
                           onchange="toggleBorderControls(); updateCanvasPreview();">
                    <label class="form-check-label">Enable</label>
                </div>
            </div>
            <div id="borderControls">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Width (px)</label>
                        <input type="number"
                               class="form-control"
                               id="border_width"
                               value="1"
                               min="1"
                               max="10"
                               onchange="updateCanvasPreview()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Color</label>
                        <input type="color"
                               class="form-control form-control-color"
                               id="border_color"
                               value="#cccccc"
                               onchange="updateCanvasPreview()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Style</label>
                        <select class="form-select" id="border_style" onchange="updateCanvasPreview()">
                            <option value="solid">Solid</option>
                            <option value="dashed">Dashed</option>
                            <option value="dotted">Dotted</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shadow Controls -->
        <div class="mb-4 p-3 border rounded">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Shadow</h6>
                <div class="form-check form-switch">
                    <input type="checkbox"
                           class="form-check-input"
                           id="shadow_show"
                           onchange="toggleShadowControls(); updateCanvasPreview();">
                    <label class="form-check-label">Enable</label>
                </div>
            </div>
            <div id="shadowControls" style="display: none;">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Blur (px)</label>
                        <input type="number"
                               class="form-control"
                               id="shadow_blur"
                               value="5"
                               min="0"
                               max="20"
                               onchange="updateCanvasPreview()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Offset X</label>
                        <input type="number"
                               class="form-control"
                               id="shadow_offset_x"
                               value="2"
                               min="-20"
                               max="20"
                               onchange="updateCanvasPreview()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Offset Y</label>
                        <input type="number"
                               class="form-control"
                               id="shadow_offset_y"
                               value="2"
                               min="-20"
                               max="20"
                               onchange="updateCanvasPreview()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Opacity</label>
                        <input type="range"
                               class="form-range"
                               id="shadow_opacity"
                               value="0.3"
                               min="0"
                               max="1"
                               step="0.1"
                               onchange="updateCanvasPreview()">
                        <small class="text-muted" id="shadow_opacity_value">0.3</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Watermark Controls -->
        <div class="p-3 border rounded">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Watermark</h6>
                <div class="form-check form-switch">
                    <input type="checkbox"
                           class="form-check-input"
                           id="watermark_show"
                           onchange="toggleWatermarkControls(); updateCanvasPreview();">
                    <label class="form-check-label">Enable</label>
                </div>
            </div>
            <div id="watermarkControls" style="display: none;">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Text</label>
                        <input type="text"
                               class="form-control"
                               id="watermark_text"
                               value="VERIFIED"
                               onchange="updateCanvasPreview()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rotation (deg)</label>
                        <input type="number"
                               class="form-control"
                               id="watermark_rotation"
                               value="-45"
                               min="-90"
                               max="90"
                               onchange="updateCanvasPreview()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Font Size</label>
                        <input type="number"
                               class="form-control"
                               id="watermark_font_size"
                               value="48"
                               min="20"
                               max="100"
                               onchange="updateCanvasPreview()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Opacity</label>
                        <input type="range"
                               class="form-range"
                               id="watermark_opacity"
                               value="0.1"
                               min="0"
                               max="1"
                               step="0.05"
                               onchange="updateCanvasPreview()">
                        <small class="text-muted" id="watermark_opacity_value">0.1</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Toggle Functions -->
<script>
function toggleBorderControls() {
    const show = $('#border_show').is(':checked');
    $('#borderControls').toggle(show);
}

function toggleShadowControls() {
    const show = $('#shadow_show').is(':checked');
    $('#shadowControls').toggle(show);
}

function toggleWatermarkControls() {
    const show = $('#watermark_show').is(':checked');
    $('#watermarkControls').toggle(show);
}

// Opacity value display
$('#shadow_opacity').on('input', function() {
    $('#shadow_opacity_value').text($(this).val());
});

$('#watermark_opacity').on('input', function() {
    $('#watermark_opacity_value').text($(this).val());
});
</script>
```

---

## 6. UPDATE getTemplateConfigFromForm()

```javascript
function getTemplateConfigFromForm(options) {
    return {
        canvas_width: parseInt(options.canvas_width) || 800,
        canvas_height: parseInt(options.canvas_height) || 600,
        background_color: options.background_color || '#ffffff',
        signature_image_url: options.signature_image_url || '',
        logo_url: options.logo_url || '',
        layout_config: options.layout_config || {
            barcode_position: { x: 50, y: 50, width: 150, height: 150 },
            signature_position: { x: 220, y: 50, width: 200, height: 100 },
            text_position: { x: 220, y: 160, width: 300, height: 120 },
            logo_position: { x: 550, y: 50, width: 120, height: 120 },
            document_info_position: { x: 50, y: 220, width: 700, height: 200 }
        },
        text_config: {
            kaprodi_name: {
                text: options.kaprodi_name || '',
                font_size: parseInt($('#kaprodi_name_size').val()) || 14,
                font_weight: $('#kaprodi_name_weight').val() || 'bold',
                color: $('#kaprodi_name_color').val() || '#000000',
                font_family: $('#kaprodi_name_family').val() || 'Arial, sans-serif'
            },
            nidn: {
                text: options.kaprodi_nidn ? 'NIDN : ' + options.kaprodi_nidn : '',
                font_size: parseInt($('#nidn_size').val()) || 12,
                font_weight: $('#nidn_weight').val() || 'normal',
                color: $('#nidn_color').val() || '#000000',
                font_family: $('#nidn_family').val() || 'Arial, sans-serif'
            },
            title: {
                text: options.kaprodi_title || '',
                font_size: parseInt($('#title_size').val()) || 12,
                font_weight: $('#title_weight').val() || 'normal',
                color: $('#title_color').val() || '#000000',
                font_family: $('#title_family').val() || 'Arial, sans-serif'
            },
            institution: {
                text: options.institution_name || '',
                font_size: parseInt($('#institution_size').val()) || 11,
                font_weight: $('#institution_weight').val() || 'normal',
                color: $('#institution_color').val() || '#666666',
                font_family: $('#institution_family').val() || 'Arial, sans-serif'
            },
            location_date: {
                text: 'Tangerang, {date}',
                font_size: parseInt($('#location_date_size').val()) || 12,
                font_weight: $('#location_date_weight').val() || 'normal',
                color: $('#location_date_color').val() || '#000000',
                font_family: $('#location_date_family').val() || 'Arial, sans-serif'
            }
        },
        style_config: {
            border: {
                show: $('#border_show').is(':checked'),
                color: $('#border_color').val() || '#cccccc',
                width: parseInt($('#border_width').val()) || 1,
                style: $('#border_style').val() || 'solid'
            },
            background: {
                color: options.background_color || '#ffffff',
                opacity: 1
            },
            shadow: {
                show: $('#shadow_show').is(':checked'),
                color: '#000000',
                blur: parseInt($('#shadow_blur').val()) || 5,
                offset_x: parseInt($('#shadow_offset_x').val()) || 2,
                offset_y: parseInt($('#shadow_offset_y').val()) || 2,
                opacity: parseFloat($('#shadow_opacity').val()) || 0.3
            },
            watermark: {
                show: $('#watermark_show').is(':checked'),
                text: $('#watermark_text').val() || 'VERIFIED',
                color: '#f0f0f0',
                font_size: parseInt($('#watermark_font_size').val()) || 48,
                opacity: parseFloat($('#watermark_opacity').val()) || 0.1,
                rotation: parseInt($('#watermark_rotation').val()) || -45
            }
        }
    };
}
```

---

## 7. STORE METHOD - SAVE STYLE CONFIG

```php
// In SignatureTemplateController::store()

// Build style config from request
$styleConfig = [
    'border' => [
        'show' => $request->has('border_show'),
        'color' => $request->input('border_color', '#cccccc'),
        'width' => (int) $request->input('border_width', 1),
        'style' => $request->input('border_style', 'solid')
    ],
    'background' => [
        'color' => $request->background_color,
        'opacity' => 1
    ],
    'shadow' => [
        'show' => $request->has('shadow_show'),
        'color' => '#000000',
        'blur' => (int) $request->input('shadow_blur', 5),
        'offset_x' => (int) $request->input('shadow_offset_x', 2),
        'offset_y' => (int) $request->input('shadow_offset_y', 2),
        'opacity' => (float) $request->input('shadow_opacity', 0.3)
    ],
    'watermark' => [
        'show' => $request->has('watermark_show'),
        'text' => $request->input('watermark_text', 'VERIFIED'),
        'color' => '#f0f0f0',
        'font_size' => (int) $request->input('watermark_font_size', 48),
        'opacity' => (float) $request->input('watermark_opacity', 0.1),
        'rotation' => (int) $request->input('watermark_rotation', -45)
    ]
];

// Build text config with styling
$textConfig = [
    'kaprodi_name' => [
        'text' => $request->kaprodi_name,
        'font_size' => (int) $request->input('kaprodi_name_size', 14),
        'font_weight' => $request->input('kaprodi_name_weight', 'bold'),
        'color' => $request->input('kaprodi_name_color', '#000000'),
        'font_family' => $request->input('kaprodi_name_family', 'Arial, sans-serif')
    ],
    // ... repeat for other text elements
];

// Create template with configs
$template = SignatureTemplate::create([
    // ... other fields
    'text_config' => $textConfig,
    'style_config' => $styleConfig,
]);
```

---

Happy coding! 🚀
