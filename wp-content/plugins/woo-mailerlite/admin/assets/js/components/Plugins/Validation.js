export function validate(ref, validations, errorMessages = {}) {
    let isValid = true;
    // Clear previous errors
    clearValidationError(ref);

    const value = ref.value.trim();

    // Perform validations
    validations.forEach((validation) => {
        if (validation === 'required' && value === '') {
            isValid = false;
            setValidationError(ref, errorMessages.required || 'This field is required.');
        }
        if (isValid && validation.startsWith('startsWith') && !value.startsWith(validation.split(':')[1])) {
            isValid = false;
            setValidationError(ref, errorMessages.startsWith || 'This field must start with http');
        }
    });

    return isValid;
}

// Helper function to clear previous errors
function clearValidationError(ref) {
    ref?.classList.remove('woo-mailerlite-input-error');

    const errorElement = ref?.nextElementSibling;
    if (errorElement && errorElement.classList.contains('woo-mailerlite-error-message')) {
        errorElement.remove();
    }
    if (ref.classList.contains('select2-hidden-accessible')) {
        ref.nextSibling.classList.remove('woo-mailerlite-input-error');
    }
}

// Helper function to set validation error
function setValidationError(ref, message) {

    ref.classList.add('woo-mailerlite-input-error');
    // if (ref.classlist)

    const errorElement = document.createElement('span');
    errorElement.classList.add('woo-mailerlite-error-message');

    errorElement.style.color = 'red';
    // errorElement.style.position = 'absolute';
    errorElement.style.top = '100%';
    errorElement.textContent = message;
    if (ref.classList.contains('select2-hidden-accessible') || ( ref.classList.length === 1)) {
        if (ref.nextSibling.type === 'span') {
            ref.nextSibling.classList.add('woo-mailerlite-input-error');
        }
        errorElement.style.position = 'absolute';
        ref.parentNode.after(errorElement);
    }

    ref.parentNode.insertBefore(errorElement, ref.nextSibling);

}

// Helper function to validate email
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}