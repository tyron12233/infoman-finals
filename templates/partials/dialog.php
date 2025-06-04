<div id="customDialog"
    class="fixed inset-0 bg-slate-800 bg-opacity-75 flex items-center justify-center p-4 z-50 hidden opacity-0 transition-opacity duration-300 ease-in-out">
    <div
        class="dialog-content bg-white rounded-lg shadow-xl p-6 w-full max-w-md transform transition-all duration-300 ease-in-out scale-95">
        <div class="flex justify-between items-center mb-4">
            <h3 id="dialogTitle" class="text-xl font-semibold text-slate-700">Dialog Title</h3>
            <button id="closeDialogBtnHeader"
                class="text-slate-400 hover:text-slate-600 text-2xl focus:outline-none">&times;</button>
        </div>
        <p id="dialogMessage" class="text-slate-600 mb-6">Dialog message goes here.</p>
        <div class="flex justify-end">
            <button id="dialogOkBtn" class="text-white font-semibold py-2 px-4 rounded">OK</button>
        </div>
    </div>
</div>