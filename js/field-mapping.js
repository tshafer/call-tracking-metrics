jQuery(document).ready(function($) {
    // Elements
    const mappingTable = $('#ctm-mapping-table');
    const addRowBtn = $('#ctm-add-mapping-row');
    const saveBtn = $('#ctm-save-mapping');
    const notice = $('#ctm-mapping-notice');
    let mapping = window.ctm_mapping_data || [];
    let fields = window.ctm_fields || [];

    function renderTable() {
        const tbody = mappingTable.find('tbody');
        tbody.empty();
        if (!mapping.length) {
            mapping.push({form_field: '', api_field: ''});
        }
        mapping.forEach((row, i) => {
            const fieldOptions = fields.map(f => `<option value="${f.id}"${row.form_field === f.id ? ' selected' : ''}>${f.label}</option>`).join('');
            tbody.append(`
                <tr>
                    <td>
                        <select class="form-field block w-full border rounded">${fieldOptions}</select>
                    </td>
                    <td>
                        <input type="text" class="api-field block w-full border rounded" value="${row.api_field || ''}" placeholder="API field name" />
                    </td>
                    <td class="text-center">
                        <button type="button" class="remove-row bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded" data-index="${i}">Remove</button>
                    </td>
                </tr>
            `);
        });
    }

    // Add row
    addRowBtn.on('click', function() {
        mapping.push({form_field: fields[0]?.id || '', api_field: ''});
        renderTable();
    });

    // Remove row
    mappingTable.on('click', '.remove-row', function() {
        const idx = $(this).data('index');
        mapping.splice(idx, 1);
        renderTable();
    });

    // Update mapping on input/select change
    mappingTable.on('change input', '.form-field, .api-field', function() {
        const row = $(this).closest('tr').index();
        mapping[row].form_field = $(this).closest('tr').find('.form-field').val();
        mapping[row].api_field = $(this).closest('tr').find('.api-field').val();
    });

    // Save mapping
    saveBtn.on('click', function() {
        saveBtn.prop('disabled', true).text('Saving...');
        notice.text('').removeClass('text-green-600 text-red-600');
        $.post(ajaxurl, {
            action: 'ctm_save_mapping',
            mapping: mapping,
            nonce: ctm_mapping.nonce
        }, function(resp) {
            saveBtn.prop('disabled', false).text('Save Mapping');
            if (resp.success) {
                notice.text('Mapping saved!').addClass('text-green-600');
            } else {
                notice.text('Failed to save mapping.').addClass('text-red-600');
            }
        });
    });

    // Initial render
    renderTable();
}); 