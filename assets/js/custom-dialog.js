function showCustomDialog(title, message, type = 'info') {
    const dialog = document.getElementById('customDialog');
    if (!dialog) {
        console.error('Custom dialog element not found.');
        alert(`${title}\n${message}`); // Fallback to alert
        return;
    }
    const dialogContent = dialog.querySelector('.dialog-content');
    const dialogTitleEl = document.getElementById('dialogTitle');
    const dialogMessageEl = document.getElementById('dialogMessage');
    let dialogOkBtn = document.getElementById('dialogOkBtn');
    let closeDialogBtnHeader = document.getElementById('closeDialogBtnHeader');

    if (!dialogContent || !dialogTitleEl || !dialogMessageEl || !dialogOkBtn || !closeDialogBtnHeader) {
        console.error('One or more custom dialog inner elements not found.');
        alert(`${title}\n${message}`); // Fallback to alert
        return;
    }

    dialogTitleEl.textContent = title;
    dialogMessageEl.textContent = message;

    // Reset and style OK button
    const baseOkBtnClasses = 'font-semibold py-2 px-4 rounded text-white focus:outline-none focus:ring-2 focus:ring-offset-2';
    dialogOkBtn.className = baseOkBtnClasses; // Reset classes
    if (type === 'error') {
        dialogOkBtn.classList.add('bg-red-500', 'hover:bg-red-600', 'focus:ring-red-500');
    } else if (type === 'success') {
        dialogOkBtn.classList.add('bg-green-500', 'hover:bg-green-600', 'focus:ring-green-500');
    } else { // 'info' or 'warning'
        dialogOkBtn.classList.add('bg-amber-500', 'hover:bg-amber-600', 'focus:ring-amber-500');
    }

    // --- Event Listener Management for Dialog Buttons ---
    // To prevent multiple listeners, we clone and replace the button
    const newOkBtn = dialogOkBtn.cloneNode(true);
    dialogOkBtn.parentNode.replaceChild(newOkBtn, dialogOkBtn);
    dialogOkBtn = newOkBtn; // Update reference

    const newCloseBtnHeader = closeDialogBtnHeader.cloneNode(true);
    closeDialogBtnHeader.parentNode.replaceChild(newCloseBtnHeader, closeDialogBtnHeader);
    closeDialogBtnHeader = newCloseBtnHeader; // Update reference
    // --- End Event Listener Management ---


    const closeDialog = () => {
        dialog.classList.add('opacity-0');
        dialogContent.classList.remove('scale-100');
        dialogContent.classList.add('scale-95');
        setTimeout(() => {
            dialog.classList.add('hidden');
            document.removeEventListener('keydown', handleEscKey); // Clean up ESC listener
        }, 300); // Match transition duration
    };

    dialogOkBtn.addEventListener('click', closeDialog);
    closeDialogBtnHeader.addEventListener('click', closeDialog);

    const handleOverlayClick = (event) => {
        if (event.target === dialog) {
            closeDialog();
        }
    };
    // Add overlay click listener (it's safe to add it each time if it's specific like this)
    dialog.addEventListener('click', handleOverlayClick);


    const handleEscKey = (event) => {
        if (event.key === 'Escape' && !dialog.classList.contains('hidden')) {
            closeDialog();
        }
    };
    document.removeEventListener('keydown', handleEscKey); // Remove previous before adding new
    document.addEventListener('keydown', handleEscKey);

    // Open dialog
    dialog.classList.remove('hidden');
    setTimeout(() => { // Allow display:block to take effect before transition
        dialog.classList.remove('opacity-0');
        dialogContent.classList.remove('scale-95');
        dialogContent.classList.add('scale-100');
    }, 10); // Small delay

    dialogOkBtn.focus(); // Focus the OK button
}