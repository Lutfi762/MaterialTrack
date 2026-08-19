<template>
  <header class="modern-header">
    <div class="header-left">
      <button class="mobile-toggle" @click="toggleMobileSidebar" aria-label="Toggle Menu">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
        </svg>
      </button>

      <div class="search-bar">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="search-icon">
          <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="$t('header.search_placeholder')"
          class="search-input"
          @input="handleSearch"
        />
        <button v-if="searchQuery" @click="clearSearch" class="search-clear">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
          </svg>
        </button>
      </div>
    </div>

    <div class="header-right">
      <button class="theme-toggle" @click="toggleDarkMode" :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'">
        <svg v-if="!isDark" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
          <path d="M6 .278a.768.768 0 0 1 .08.858 7.207 7.207 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
          <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
        </svg>
      </button>

      <LanguageSwitcher />

      <div class="notification-wrapper">
        <button class="notification-btn" @click.stop="toggleNotifications" :class="{ active: showNotifications }">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
          </svg>
          <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
        </button>

        <div v-if="showNotifications" class="notification-panel">
          <div class="notification-header">
            <h4>{{ $t('header.notifications') }}</h4>
            <div class="d-flex align-items-center gap-2">
              <span class="notification-count">{{ unreadCount }}</span>
              <button v-if="unreadCount > 0" @click.stop="markAllRead" class="mark-all-btn">Tandai sudah dibaca</button>
            </div>
          </div>

          <div class="notification-list">
            <div v-if="notifications.length === 0" class="notification-empty">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zm.995-14.901a1 1 0 1 0-1.99 0A5.002 5.002 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901z"/>
              </svg>
              <p>{{ $t('header.no_notifications') }}</p>
            </div>

            <div
              v-for="n in notifications"
              :key="n.id"
              class="notification-item"
              :class="{ unread: !n.is_read }"
              @click.stop="n.link && (window.location.href = n.link)"
            >
              <div class="notification-icon" :class="`icon-${n.type}`">
                <svg v-if="n.type === 'low_stock' || n.type === 'out_of_stock'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                <svg v-else-if="n.type === 'pending_approval'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                </svg>
                <svg v-else-if="n.type === 'expiring_item'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8.515 1.019A7 7 0 0 0 8 1a7 7 0 0 0-8 8 7 7 0 0 0 7 7h.5a.5.5 0 0 0 0-1H8a6 6 0 1 1 0-12h.032a.5.5 0 0 0 .483-.385zM3.228 5.6A6 6 0 0 1 8 2a6 6 0 0 1 4.772 8.378l-.348-.174a.5.5 0 0 0-.483.385H3.5a.5.5 0 0 0-.272.9zM8 14a5 5 0 0 0 4.473-7.355A.5.5 0 0 0 12 6.5a.5.5 0 0 0-.5-.5H8V2a5 5 0 0 0-4.473 2.645A.5.5 0 0 0 3.5 6.5a.5.5 0 0 0 .5.5h4v4.5z"/>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                  <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                </svg>
              </div>
              <div class="notification-content">
                <div class="notification-title">{{ n.title }}</div>
                <div class="notification-message">{{ n.message }}</div>
                <div class="notification-time">{{ new Date(n.created_at).toLocaleString('id-ID') }}</div>
              </div>
            </div>
          </div>

          <div class="notification-footer">
            <Link href="/apps/items" class="notification-link">Lihat semua item</Link>
          </div>
        </div>
      </div>

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
          <Link href="/profile" class="dropdown-profile-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H4s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
            </svg>
            Profil Saya
          </Link>
          <Link href="/logout" method="post" as="button" class="dropdown-logout">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
              <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
            </svg>
            {{ $t('header.logout') }}
          </Link>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import LanguageSwitcher from './LanguageSwitcher.vue'

const showDropdown = ref(false)
const showNotifications = ref(false)
const searchQuery = ref('')
const notifications = ref([])
const unreadCount = ref(0)
const isLoadingNotifications = ref(false)
const isDark = ref(false)

function toggleDarkMode() {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark-mode', isDark.value)
  localStorage.setItem('darkMode', isDark.value ? '1' : '0')
}

const userRoleLabel = computed(() => {
  const page = usePage()
  const roles = page.props.auth?.user?.roles ?? []
  if (roles.includes('super_admin')) return 'Super Admin'
  if (roles.includes('admin')) return 'Administrator'
  if (roles.includes('department_user')) return 'Department User'
  return 'User'
})

function toggleMobileSidebar() {
  window.dispatchEvent(new CustomEvent('toggle-mobile-sidebar'))
}

function toggleNotifications() {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value) {
    loadNotifications()
  }
}

async function loadNotifications() {
  if (isLoadingNotifications.value) return
  isLoadingNotifications.value = true

  try {
    const response = await fetch('/apps/notifications', {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    if (response.ok) {
      const data = await response.json()
      notifications.value = data.notifications
      unreadCount.value = data.unread_count
    }
  } catch (error) {
    console.error('Failed to load notifications:', error)
  } finally {
    isLoadingNotifications.value = false
  }
}

async function markAsRead(id) {
  try {
    await fetch(`/apps/notifications/${id}/read`, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
    })
    const n = notifications.value.find(x => x.id === id)
    if (n && !n.is_read) { n.is_read = true; unreadCount.value = Math.max(0, unreadCount.value - 1) }
  } catch (e) {}
}

async function markAllRead() {
  try {
    await fetch('/apps/notifications/read-all', {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
    })
    notifications.value.forEach(n => n.is_read = true)
    unreadCount.value = 0
  } catch (e) {}
}

function handleSearch() {
  // Placeholder for search functionality
  // Will be implemented when backend search endpoint is ready
  console.log('Searching:', searchQuery.value)
}

function clearSearch() {
  searchQuery.value = ''
}

function closeDropdown() {
  showDropdown.value = false
}

function closeNotifications() {
  showNotifications.value = false
}

function closeAll() {
  showDropdown.value = false
  showNotifications.value = false
}

onMounted(() => {
  document.addEventListener('click', closeAll)

  // Initialize dark mode from localStorage
  const savedDark = localStorage.getItem('darkMode')
  if (savedDark === '1') {
    isDark.value = true
    document.documentElement.classList.add('dark-mode')
  }
})

onUnmounted(() => {
  document.removeEventListener('click', closeAll)
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
  flex: 1;
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

/* Search Bar */
.search-bar {
  position: relative;
  display: flex;
  align-items: center;
  max-width: 480px;
  width: 100%;
}

.search-icon {
  position: absolute;
  left: 12px;
  color: #9ca3af;
  pointer-events: none;
}

.search-input {
  width: 100%;
  height: 40px;
  padding: 0 36px 0 36px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 14px;
  color: #111827;
  background: #fafafa;
  transition: all 0.2s;
}

.search-input::placeholder {
  color: #9ca3af;
}

.search-input:focus {
  outline: none;
  background: white;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.search-clear {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.search-clear:hover {
  background: #f3f4f6;
  color: #374151;
}

@media (max-width: 768px) {
  .search-bar {
    display: none;
  }
}

/* Header Right */
.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Dark Mode Toggle */
.theme-toggle {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: none;
  border: 1px solid #e5e7eb;
  color: #374151;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.theme-toggle:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

/* Notification */
.notification-wrapper {
  position: relative;
}

.notification-btn {
  position: relative;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: none;
  border: 1px solid #e5e7eb;
  color: #374151;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.notification-btn:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

.notification-btn.active {
  background: #f9fafb;
  border-color: #6366f1;
  color: #6366f1;
}

.notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 700;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
}

.notification-panel {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 380px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  z-index: 100;
  max-height: 500px;
  display: flex;
  flex-direction: column;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}

.notification-header h4 {
  font-size: 15px;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.notification-count {
  font-size: 12px;
  padding: 3px 8px;
  background: #e5e7eb;
  color: #374151;
  border-radius: 12px;
  font-weight: 600;
}

.mark-all-btn {
  background: none;
  border: none;
  color: #6366f1;
  font-size: 11px;
  cursor: pointer;
  padding: 2px 6px;
  border-radius: 4px;
}
.mark-all-btn:hover {
  background: #eef2ff;
}

.notification-list {
  flex: 1;
  overflow-y: auto;
  max-height: 360px;
}

.notification-list::-webkit-scrollbar {
  width: 6px;
}

.notification-list::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.notification-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px 20px;
  color: #9ca3af;
}

.notification-empty svg {
  margin-bottom: 12px;
  opacity: 0.5;
}

.notification-empty p {
  margin: 0;
  font-size: 14px;
}

.notification-item {
  display: flex;
  gap: 12px;
  padding: 14px 20px;
  border-bottom: 1px solid #f3f4f6;
  transition: background 0.2s;
  cursor: pointer;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item.unread {
  background: #f0f9ff;
}

.notification-item.unread .notification-title {
  font-weight: 700;
}

.notification-item:hover {
  background: #f9fafb;
}

.notification-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.notification-icon.icon-warning,
.notification-icon.icon-low_stock {
  background: #fff7ed;
  color: #f97316;
}

.notification-icon.icon-danger,
.notification-icon.icon-out_of_stock {
  background: #fef2f2;
  color: #ef4444;
}

.notification-icon.icon-pending_approval {
  background: #fefce8;
  color: #eab308;
}

.notification-icon.icon-expiring_item {
  background: #fef2f2;
  color: #dc2626;
}

.notification-icon.icon-info {
  background: #eff6ff;
  color: #3b82f6;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-size: 13px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 4px;
}

.notification-message {
  font-size: 12px;
  color: #6b7280;
  line-height: 1.4;
  margin-bottom: 4px;
}

.notification-time {
  font-size: 11px;
  color: #9ca3af;
}

.notification-footer {
  padding: 12px 20px;
  border-top: 1px solid #e5e7eb;
  background: #fafafa;
}

.notification-link {
  display: block;
  text-align: center;
  font-size: 13px;
  color: #6366f1;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}

.notification-link:hover {
  color: #4f46e5;
  text-decoration: none;
}

@media (max-width: 640px) {
  .notification-panel {
    width: calc(100vw - 32px);
    right: -8px;
  }
}

/* User Dropdown */
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

.dropdown-profile-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  background: none;
  border: none;
  border-bottom: 1px solid #e5e7eb;
  color: #374151;
  font-size: 13.5px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
  text-decoration: none;
  width: 100%;
}

.dropdown-profile-link:hover {
  background: #f9fafb;
  color: #111827;
  text-decoration: none;
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