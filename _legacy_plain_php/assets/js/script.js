document.addEventListener('DOMContentLoaded', () => {
    const editModal = document.getElementById('editStudentModal');
    const deleteModal = document.getElementById('deleteStudentModal');

    if (editModal) {
        editModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            document.getElementById('edit_id').value = button.getAttribute('data-id');
            document.getElementById('edit_fullname').value = button.getAttribute('data-fullname');
            document.getElementById('edit_email').value = button.getAttribute('data-email');
            document.getElementById('edit_course').value = button.getAttribute('data-course');
        });
    }

    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            document.getElementById('delete_id').value = button.getAttribute('data-id');
            document.getElementById('delete_name').textContent = button.getAttribute('data-name');
        });
    }

    document.querySelectorAll('.alert').forEach((alert) => {
        window.setTimeout(() => {
            const instance = bootstrap.Alert.getOrCreateInstance(alert);
            instance.close();
        }, 4500);
    });
});
