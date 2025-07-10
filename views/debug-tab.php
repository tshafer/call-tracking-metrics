<?php
// Debug tab view
?>
<div class="mb-12">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <div class="flex items-center mb-6 border-b border-blue-100 pb-4">
            <svg class="w-7 h-7 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9" /></svg>
            <h2 class="text-2xl font-bold text-blue-800 tracking-tight">Debug</h2>
        </div>
        <div class="mb-6 text-gray-700">Enable debug mode to log all API requests, responses, and errors. Use this for troubleshooting and support.</div>
        <form method="post" class="flex flex-wrap gap-4 mb-6">
            <button type="submit" name="toggle_debug" class="<?= $debugEnabled ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-600 hover:bg-gray-700 text-white' ?> font-bold px-6 py-2 rounded shadow transition"><?= $debugEnabled ? 'Disable Debug Mode' : 'Enable Debug Mode' ?></button>
            <button type="submit" name="clear_debug_log" class="bg-red-500 hover:bg-red-600 text-white font-bold px-6 py-2 rounded shadow transition">Clear Debug Log</button>
        </form>
        <?php if ($debugEnabled): ?>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4 rounded">Debug mode is <b>enabled</b>. All API requests, responses, and errors will be logged here.</div>
        <?php else: ?>
            <div class="bg-gray-50 border-l-4 border-gray-300 p-4 mb-4 rounded">Debug mode is <b>disabled</b>. Enable it to log detailed plugin activity.</div>
        <?php endif; ?>
        <div class="bg-white rounded shadow p-4">
            <?php if (empty($log)): ?>
                <div class="text-gray-500">No debug log entries found.</div>
            <?php else: ?>
                <div class="overflow-x-auto"><table class="min-w-full bg-white border border-gray-200 rounded-xl shadow-sm text-sm">
                    <thead class="sticky top-0 z-10 bg-blue-50 text-blue-800 font-semibold">
                        <tr><th class="px-4 py-2 border-b text-left">Date</th><th class="px-4 py-2 border-b text-left">Message</th></tr>
                    </thead>
                    <tbody>
                    <?php $rowAlt = false; foreach (array_reverse($log) as $entry):
                        $rowBg = $rowAlt ? 'bg-blue-50' : 'bg-white';
                    ?>
                        <tr class="<?= $rowBg ?> hover:bg-blue-100 transition">
                            <td class="px-4 py-2 border-b font-mono"><?= esc_html($entry['date'] ?? '') ?></td>
                            <td class="px-4 py-2 border-b whitespace-pre-line"><?= esc_html($entry['message'] ?? '') ?></td>
                        </tr>
                    <?php $rowAlt = !$rowAlt; endforeach; ?>
                    </tbody>
                </table></div>
            <?php endif; ?>
        </div>
    </div>
</div> 