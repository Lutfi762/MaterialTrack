import { usePage } from '@inertiajs/vue3'

/**
 * Composable untuk mengecek hak akses / permission di Vue 3
 */
export function usePermission() {
  const page = usePage()

  /**
   * Cek apakah user memiliki SALAH SATU permission yang ditentukan
   * @param {Array|String} permissions - Contoh: 'items.create' atau ['items.create', 'items.edit']
   * @returns {Boolean}
   */
  const hasAnyPermission = (permissions) => {
    // Ambil array permissions dari shared props Inertia
    const allPermissions = page.props.auth?.permissions || []

    // Jika parameter berupa string tunggal, ubah ke array
    const targetPermissions = Array.isArray(permissions) ? permissions : [permissions]

    // Cek apakah ada minimal 1 permission yang cocok
    return targetPermissions.some((permission) => allPermissions.includes(permission))
  }

  return {
    hasAnyPermission,
  }
}