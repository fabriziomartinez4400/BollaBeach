function loadStart(ref) {
    ref.classList.add('woo-mailerlite-loading');
    ref.disabled = true;
}
function loadEnd(ref) {
    ref.classList.remove('woo-mailerlite-loading');
    ref.disabled = false;
}
export { loadStart, loadEnd };