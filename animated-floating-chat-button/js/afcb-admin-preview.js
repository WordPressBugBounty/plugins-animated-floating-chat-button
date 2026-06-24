/**
 * Animated Floating Chat Button
 * Admin Live Preview
 * Version: 1.0.2
 */

document.addEventListener('DOMContentLoaded', function () {

    // Settings Fields
    const horizontalPosition = document.querySelector('[name="afcb_chat_button_horizontal_position"]');
    const verticalPosition = document.querySelector('[name="afcb_chat_button_vertical_position"]');
    const horizontalOffset = document.querySelector('[name="afcb_chat_button_horizontal_offset"]');
    const verticalOffset = document.querySelector('[name="afcb_chat_button_vertical_offset"]');
    const previewButton = document.querySelector('#afcb-admin-floating-preview');

    // Update preview position in real time
    function updatePreviewPosition() {
        if (!previewButton) {
            return;
        }

        previewButton.style.left = 'auto';
        previewButton.style.right = 'auto';
        previewButton.style.top = 'auto';
        previewButton.style.bottom = 'auto';

        const horizontalValue = horizontalPosition ? horizontalPosition.value : 'right';
        const verticalValue = verticalPosition ? verticalPosition.value : 'bottom';
        const horizontalSpace = horizontalOffset && horizontalOffset.value !== '' ? horizontalOffset.value : '30';
        const verticalSpace = verticalOffset && verticalOffset.value !== '' ? verticalOffset.value : '30';

        previewButton.style[horizontalValue] = horizontalSpace + 'px';
        previewButton.style[verticalValue] = verticalSpace + 'px';
    }

    // Listen for settings changes
    [horizontalPosition, verticalPosition, horizontalOffset, verticalOffset].forEach(function (field) {
        if (field) {
            field.addEventListener('change', updatePreviewPosition);
            field.addEventListener('input', updatePreviewPosition);
        }
    });

    updatePreviewPosition();
});