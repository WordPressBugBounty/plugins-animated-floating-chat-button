/**
 * Animated Floating Chat Button
 * Admin Live Preview
 *
 * @version 1.0.3
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const previewButton = document.querySelector('#afcb-admin-floating-preview');

    if (!previewButton) {
        return;
    }

    const previewStatus = document.querySelector('.afcb-preview-status');
    const previewStatusText = document.querySelector('#afcb-preview-status-text');
    const horizontalPositionFields = document.querySelectorAll(
        '[name="afcb_chat_button_horizontal_position"]'
    );
    const verticalPositionFields = document.querySelectorAll(
        '[name="afcb_chat_button_vertical_position"]'
    );
    const horizontalOffset = document.querySelector(
        '[name="afcb_chat_button_horizontal_offset"]'
    );
    const verticalOffset = document.querySelector(
        '[name="afcb_chat_button_vertical_offset"]'
    );
    const horizontalRange = document.querySelector('#afcb-horizontal-range');
    const verticalRange = document.querySelector('#afcb-vertical-range');
    const visibilityField = document.querySelector(
        '[name="afcb_chat_button_visibility"]'
    );

    /**
     * Return the value of the selected radio field.
     *
     * @param {NodeList} fields Radio fields.
     * @param {string} fallback Fallback value.
     * @returns {string}
     */
    function getSelectedValue(fields, fallback) {
        const selectedField = Array.from(fields).find(function (field) {
            return field.checked;
        });

        return selectedField ? selectedField.value : fallback;
    }

    /**
     * Return a safe numeric offset value.
     *
     * @param {HTMLInputElement|null} field Input field.
     * @param {number} fallback Fallback value.
     * @returns {number}
     */
    function getOffsetValue(field, fallback) {
        if (!field || field.value === '') {
            return fallback;
        }

        const value = parseInt(field.value, 10);

        if (Number.isNaN(value)) {
            return fallback;
        }

        return Math.max(0, Math.min(200, value));
    }

    /**
     * Update the preview button position.
     *
     * @returns {void}
     */
    function updatePreviewPosition() {
        const horizontalPosition = getSelectedValue(
            horizontalPositionFields,
            'right'
        );
        const verticalPosition = getSelectedValue(
            verticalPositionFields,
            'bottom'
        );
        const horizontalSpace = getOffsetValue(horizontalOffset, 30);
        const verticalSpace = getOffsetValue(verticalOffset, 30);

        previewButton.style.left = 'auto';
        previewButton.style.right = 'auto';
        previewButton.style.top = 'auto';
        previewButton.style.bottom = 'auto';

        previewButton.style[horizontalPosition] = horizontalSpace + 'px';
        previewButton.style[verticalPosition] = verticalSpace + 'px';
    }

    /**
     * Update preview visibility and status text.
     *
     * @returns {void}
     */
    function updatePreviewVisibility() {
        const isVisible = Boolean(visibilityField && visibilityField.checked);
        const visibleText =
            window.afcbAdminPreview && window.afcbAdminPreview.visibleText
                ? window.afcbAdminPreview.visibleText
                : 'Button is visible';
        const hiddenText =
            window.afcbAdminPreview && window.afcbAdminPreview.hiddenText
                ? window.afcbAdminPreview.hiddenText
                : 'Button is hidden';

        previewButton.classList.toggle('is-hidden', !isVisible);

        if (previewStatus) {
            previewStatus.classList.toggle('is-hidden', !isVisible);
        }

        if (previewStatusText) {
            previewStatusText.textContent = isVisible ? visibleText : hiddenText;
        }
    }

    /**
     * Keep a range field and number field synchronized.
     *
     * @param {HTMLInputElement|null} rangeField Range input.
     * @param {HTMLInputElement|null} numberField Number input.
     * @returns {void}
     */
    function synchronizeFields(rangeField, numberField) {
        if (!rangeField || !numberField) {
            return;
        }

        rangeField.addEventListener('input', function () {
            numberField.value = rangeField.value;
            updatePreviewPosition();
        });

        numberField.addEventListener('input', function () {
            const value = getOffsetValue(numberField, 0);

            numberField.value = value;
            rangeField.value = value;
            updatePreviewPosition();
        });
    }

    horizontalPositionFields.forEach(function (field) {
        field.addEventListener('change', updatePreviewPosition);
    });

    verticalPositionFields.forEach(function (field) {
        field.addEventListener('change', updatePreviewPosition);
    });

    synchronizeFields(horizontalRange, horizontalOffset);
    synchronizeFields(verticalRange, verticalOffset);

    if (visibilityField) {
        visibilityField.addEventListener('change', updatePreviewVisibility);
    }

    updatePreviewPosition();
    updatePreviewVisibility();
});