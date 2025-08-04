jQuery(document).ready(function($) {
    // Elements
    const formType = $('#ctm_form_type');
    const formId = $('#ctm_form_id');
    const mappingTableContainer = $('#ctm-mapping-table-container');
    let fields = [];
    let mapping = [];

    // Fetch forms when form type changes
    formType.on('change', function() {
        const type = $(this).val();
        formId.html('<option value="">Loading...</option>');
        $.post(ctmMappingAjax.ajax_url, {
            action: 'ctm_get_forms',
            form_type: type,
            nonce: ctmMappingAjax.nonce
        }, function(resp) {
            if (resp.success) {
                let options = '<option value="">Select a form</option>';
                resp.data.forEach(f => {
                    options += `<option value="${f.id}">${f.title}</option>`;
                });
                formId.html(options);
            } else {
                formId.html('<option value="">No forms found</option>');
            }
        });
    });

    // Fetch fields when form is selected
    formId.on('change', function() {
        const type = formType.val();
        const id = $(this).val();
        mappingTableContainer.html('<div class="text-gray-500">Loading fields...</div>');
        $.post(ctmMappingAjax.ajax_url, {
            action: 'ctm_get_fields',
            form_type: type,
            form_id: id,
            nonce: ctmMappingAjax.nonce
        }, function(resp) {
            if (resp.success) {
                fields = resp.data;
                renderMappingTable();
            } else {
                mappingTableContainer.html('<div class="text-red-500">No fields found.</div>');
            }
        });
    });

    // Render the mapping table UI with Tailwind CSS
    function renderMappingTable() {
        let html = '<table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm"><thead><tr>' +
            '<th class="px-4 py-2 border-b text-left bg-gray-50">Form Field</th>' +
            '<th class="px-4 py-2 border-b text-left bg-gray-50">API Field</th>' +
            '<th class="px-4 py-2 border-b text-left bg-gray-50">Transformation</th>' +
            '<th class="px-4 py-2 border-b text-left bg-gray-50">Default Value</th>' +
            '<th class="px-4 py-2 border-b bg-gray-50"></th>' +
            '</tr></thead><tbody>';
        if (!mapping.length && fields.length) {
            // Start with one row per field
            mapping = fields.map(f => ({ form_field: f.id, api_field: '', transform: '', default: '' }));
        }
        mapping.forEach((row, i) => {
            html += '<tr class="hover:bg-gray-50">' +
                `<td class="px-4 py-2 border-b"><select class="ctm-form-field block w-full rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">${fields.map(f => `<option value="${f.id}"${row.form_field===f.id?' selected':''}>${f.label}</option>`).join('')}</select></td>` +
                `<td class="px-4 py-2 border-b"><input type="text" class="ctm-api-field block w-full rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500" value="${row.api_field||''}" placeholder="API field name"></td>` +
                `<td class="px-4 py-2 border-b"><select class="ctm-transform block w-full rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500"><option value="">None</option><option value="uppercase"${row.transform==='uppercase'?' selected':''}>Uppercase</option><option value="lowercase"${row.transform==='lowercase'?' selected':''}>Lowercase</option><option value="join"${row.transform==='join'?' selected':''}>Join (CSV)</option><option value="date_iso"${row.transform==='date_iso'?' selected':''}>Date (ISO)</option></select></td>` +
                `<td class="px-4 py-2 border-b"><input type="text" class="ctm-default block w-full rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500" value="${row.default||''}" placeholder="Default value"></td>` +
                `<td class="px-4 py-2 border-b text-center"><button type="button" class="ctm-remove-row bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition">Remove</button></td>` +
                '</tr>';
        });
        html += '</tbody></table>';
        html += '<div class="mt-4 flex gap-2">' +
            '<button type="button" class="bg-blue-600 hover:bg-blue-700 text-white! px-4 py-2 rounded shadow transition " id="ctm-add-row">Add Mapping Row</button>' +
            '</div>';
        mappingTableContainer.html(html);
    }

    // Add mapping row
    mappingTableContainer.on('click', '#ctm-add-row', function() {
        mapping.push({ form_field: fields[0]?.id || '', api_field: '', transform: '', default: '' });
        renderMappingTable();
    });

    // Remove mapping row
    mappingTableContainer.on('click', '.ctm-remove-row', function() {
        const idx = $(this).closest('tr').index();
        mapping.splice(idx, 1);
        renderMappingTable();
    });

    // Update mapping on input/select change
    mappingTableContainer.on('change', '.ctm-form-field, .ctm-api-field, .ctm-transform, .ctm-default', function() {
        const row = $(this).closest('tr').index();
        mapping[row].form_field = $(this).closest('tr').find('.ctm-form-field').val();
        mapping[row].api_field = $(this).closest('tr').find('.ctm-api-field').val();
        mapping[row].transform = $(this).closest('tr').find('.ctm-transform').val();
        mapping[row].default = $(this).closest('tr').find('.ctm-default').val();
    });

    // Save mapping on form submit
    $('#ctm-field-mapping-form').on('submit', function(e) {
        if (!fields.length) return;
        e.preventDefault();
        const type = formType.val();
        const id = formId.val();
        $.post(ctmMappingAjax.ajax_url, {
            action: 'ctm_save_mapping',
            form_type: type,
            form_id: id,
            mapping: mapping,
            nonce: ctmMappingAjax.nonce
        }, function(resp) {
            if (resp.success) {
                alert('Mapping saved!');
            } else {
                alert('Failed to save mapping.');
            }
        });
    });

    // Preview mapping (scaffold)
    $('#ctm-preview-mapping').on('click', function() {
        alert('Preview feature coming soon!');
    });
}); 