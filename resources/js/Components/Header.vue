<template>
  <header class="modern-header">
    <div class="header-left">
      <button class="mobile-toggle" @click="toggleMobileSidebar" aria-label="Toggle Menu">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
        </svg>
      </button>
    </div>

    <div class="header-right">
      <div class="user-dropdown" @click.stop="showDropdown = !showDropdown">
        <div class="user-avatar">
          <img :src="`https://ui-avatars.com/api/?name=${$page.props.auth?.user?.name || 'User'}&background=7c3aed&color=ffffff&size=100`" alt="User Avatar" />
        </div>
        <div class="user-info">
          <div class="user-name">{{ $page.props.auth?.user?.name }}</div>
          <div class="user-role">{{ userRoleLabel }}</div>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="dropdown-arrow">
          <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
        </svg>

        <div v-if="showDropdown" class="dropdown-panel">
          <div class="dropdown-header">
            <div class="dropdown-user-name">{{ $page.props.auth?.user?.name }}</div>
            <div class="dropdown-user-email">{{ $page.props.auth?.user?.email }}</div>
          </div>
          <Link href="/logout" method="post" as="button" class="dropdown-logout">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
              <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
            </svg>
            Logout
          </Link>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'

const showDropdown = ref(false)

const userRoleLabel = computed(() => {
  const page = usePage()
  const roles = page.props.auth?.user?.roles ?? []
  if (roles.includes('super_admin')) return 'Super Admin'
  if (roles.includes('admin')) return 'Administrator'
  if (roles.includes('department_user')) return 'Department User'
  return 'User'
})

function toggleMobileSidebar() {
  // Mobile sidebar toggle handled by parent layout
  window.dispatchEvent(new CustomEvent('toggle-mobile-sidebar'))
}

function closeDropdown() {
  showDropdown.value = false
}

onMounted(() => {
  document.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
})
</script>

<style scoped>
.modern-header {
  height: 64px;
  background: white;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  position: sticky;
  top: 0;
  z-index: 90;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.mobile-toggle {
  display: none;
  background: none;
  border: none;
  color: #374151;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s;
}

.mobile-toggle:hover {
  background: #f3f4f6;
  color: #111827;
}

@media (max-width: 768px) {
  .mobile-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

.header-right {
  margin-left: auto;
}

.user-dropdown {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 6px 12px 6px 6px;
  border-radius: 12px;
  cursor: pointer;
  transition: background 0.2s;
  position: relative;
  user-select: none;
}

.user-dropdown:hover {
  background: #f9fafb;
}

.user-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

@media (max-width: 640px) {
  .user-info {
    display: none;
  }
}

.user-name {
  font-size: 13.5px;
  font-weight: 600;
  color: #111827;
  line-height: 1.2;
}

.user-role {
  font-size: 11px;
  color: #6b7280;
  line-height: 1.2;
}

.dropdown-arrow {
  color: #9ca3af;
  flex-shrink: 0;
}

.dropdown-panel {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  min-width: 240px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  z-index: 100;
}

.dropdown-header {
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}

.dropdown-user-name {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 4px;
}

.dropdown-user-email {
  font-size: 12px;
  color: #6b7280;
}

.dropdown-logout {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  background: none;
  border: none;
  color: #dc2626;
  font-size: 13.5px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
  text-align: left;
}

.dropdown-logout:hover {
  background: #fef2f2;
}
</style>