import Swal from 'sweetalert2';

export function confirmDelete(message = 'Data ini akan dihapus permanen.') {
    return Swal.fire({
        title: 'Yakin?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => result.isConfirmed);
}