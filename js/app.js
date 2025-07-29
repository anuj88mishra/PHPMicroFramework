function JS_READY() {
    /* Add hide functionality for notifications */
    (document.querySelectorAll('.notification .delete') || []).forEach(($delete) => {
        const $notification = $delete.parentNode;
    
        $delete.addEventListener('click', () => {
          $notification.parentNode.removeChild($notification);
        });
      });
}
document.addEventListener('DOMContentLoaded', JS_READY());
