<?php
// Field Mapping tab view
?>
<div class="mb-12">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <div class="flex items-center mb-6 border-b border-blue-100 pb-4">
            <svg class="w-7 h-7 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h3m4 4v1a3 3 0 01-3 3H7a3 3 0 01-3-3v-1a9 9 0 0118 0z" /></svg>
            <h2 class="text-2xl font-bold text-blue-800 tracking-tight">Field Mapping</h2>
        </div>
        <form id="ctm-field-mapping-form" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                <div>
                    <label class="block mb-2 text-gray-600 font-medium">Form Type</label>
                    <select id="ctm_form_type" class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base">
                        <option value="gf">Gravity Forms</option>
                        <option value="cf7">Contact Form 7</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-gray-600 font-medium">Form</label>
                    <select id="ctm_form_id" class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base"></select>
                </div>
            </div>
            <div id="ctm-mapping-table-container"></div>
            <div class="flex gap-4 mt-6">
                <button type="button" id="ctm-save-mapping" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2 rounded-lg shadow transition">Save Mapping</button>
                <button type="button" id="ctm-preview-mapping" class="bg-gray-500 hover:bg-gray-600 text-white font-bold px-6 py-2 rounded-lg shadow transition">Preview</button>
            </div>
        </form>
        <div id="ctm-mapping-preview" class="mt-8 hidden"></div>
    </div>
</div> 