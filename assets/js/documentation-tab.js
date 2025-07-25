// Main tab switcher
const mainTabButtons = document.querySelectorAll('.main-doc-tab');
const mainTabPanels = document.querySelectorAll('.main-doc-tab-panel');
mainTabButtons.forEach(btn => {
    btn.addEventListener('click', function() {
        mainTabButtons.forEach(b => b.classList.remove('text-white', 'border-blue-600', 'font-semibold'));
        mainTabButtons.forEach(b => b.classList.add('text-gray-700', 'border-transparent'));
        this.classList.add('text-white', 'border-blue-600', 'font-semibold');
        this.classList.remove('text-gray-700', 'border-transparent');
        const tab = this.getAttribute('data-tab');
        mainTabPanels.forEach(panel => {
            if (panel.getAttribute('data-tab') === tab) {
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        });
        // Reset subtabs to first for each main tab
        if(tab === 'debug') {
            showSubtab('debug', 'system-info');
        } else if(tab === 'api') {
            showSubtab('api', 'overview');
        } else if(tab === 'cti') {
            showSubtab('cti', 'overview');
        }
    });
});
// Debug subtab switcher
function showSubtab(group, subtab) {
    document.querySelectorAll('.' + group + '-subtab').forEach(btn => {
        if(btn.getAttribute('data-subtab') === subtab) {
            btn.classList.add('text-white', 'border-blue-600', 'font-semibold');
            btn.classList.remove('text-gray-700', 'border-transparent');
        } else {
            btn.classList.remove('text-white', 'border-blue-600', 'font-semibold');
            btn.classList.add('text-gray-700', 'border-transparent');
        }
    });
    document.querySelectorAll('.' + group + '-subtab-panel').forEach(panel => {
        if(panel.getAttribute('data-subtab') === subtab) {
            panel.classList.remove('hidden');
        } else {
            panel.classList.add('hidden');
        }
    });
}
document.querySelectorAll('.debug-subtab').forEach(btn => {
    btn.addEventListener('click', function() {
        showSubtab('debug', this.getAttribute('data-subtab'));
    });
});
document.querySelectorAll('.api-subtab').forEach(btn => {
    btn.addEventListener('click', function() {
        showSubtab('api', this.getAttribute('data-subtab'));
    });
});
document.querySelectorAll('.cti-subtab').forEach(btn => {
    btn.addEventListener('click', function() {
        showSubtab('cti', this.getAttribute('data-subtab'));
    });
});
// Default to General tab and first subtab for each group
if(mainTabPanels.length) mainTabPanels[0].classList.remove('hidden');
showSubtab('debug', 'system-info');
showSubtab('api', 'overview');
showSubtab('cti', 'overview'); 