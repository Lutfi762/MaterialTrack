<template>
    <div class="sidebar" :class="{ 'sidebar-collapsed': collapsed }">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <img :src="logoUrl" alt="MaterialTrack" class="brand-logo-img" />
            </div>
            <span class="brand-name">MaterialTrack</span>
            <button class="sidebar-toggle" @click="toggle">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </button>
        </div>

        <nav class="sidebar-nav">

            <div class="nav-section-label">MAIN</div>

            <Link href="/apps/dashboard" class="nav-item" :class="{ active: $page.url.startsWith('/apps/dashboard') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.389.389 0 0 0-.029-.518z"/>
                        <path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A7.988 7.988 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.532 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3z"/>
                    </svg>
                </span>
                <span class="nav-label">Dashboard</span>
            </Link>

            <template v-if="!isDepartmentUser">
                <div class="nav-section-label">MASTER DATA</div>

                <Link href="/apps/categories" class="nav-item" :class="{ active: $page.url.startsWith('/apps/categories') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4H2.19zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707z"/>
                        </svg>
                    </span>
                    <span class="nav-label">Kategori Material</span>
                </Link>
            </template>

            <Link href="/apps/items" class="nav-item" :class="{ active: $page.url.startsWith('/apps/items') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zm-7.5 10.522V6.84L1 4.24v7.922l6.5 2.6z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ isDepartmentUser ? 'Daftar Material' : 'Data Items/Material' }}</span>
            </Link>

            <Link v-if="!isDepartmentUser" href="/apps/locations" class="nav-item" :class="{ active: $page.url.startsWith('/apps/locations') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z"/>
                        <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                    </svg>
                </span>
                <span class="nav-label">Lokasi Gudang</span>
            </Link>

            <div class="nav-section-label">TRANSAKSI STOK</div>

            <Link v-if="!isDepartmentUser" href="/apps/stock-ins" class="nav-item" :class="{ active: $page.url.startsWith('/apps/stock-ins') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm10.096 3.146a.5.5 0 0 0-.707-.708L5.5 8.293V5.5a.5.5 0 0 0-1 0v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1H6.207l3.889-3.854z"/>
                    </svg>
                </span>
                <span class="nav-label">Stok Masuk</span>
                <span class="nav-badge badge-success">IN</span>
            </Link>

            <Link href="/apps/stock-outs" class="nav-item" :class="{ active: $page.url.startsWith('/apps/stock-outs') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm5.904 8.854a.5.5 0 0 0 .707.708L10.5 7.707V10.5a.5.5 0 0 0 1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 0 0 1h2.793l-3.889 3.854z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ isDepartmentUser ? 'Input Pemakaian' : 'Stok Keluar' }}</span>
                <span class="nav-badge badge-danger">OUT</span>
            </Link>

            <Link v-if="!isDepartmentUser" href="/apps/stock-movements" class="nav-item" :class="{ active: $page.url.startsWith('/apps/stock-movements') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0v1z"/>
                        <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5z"/>
                    </svg>
                </span>
                <span class="nav-label">Riwayat Pergerakan</span>
            </Link>

            <Link v-if="!isDepartmentUser" href="/apps/stock-opnames" class="nav-item" :class="{ active: $page.url.startsWith('/apps/stock-opnames') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M6 1a1 1 0 0 0-1 1v1H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-1V2a1 1 0 1 0-2 0v1H7V2a1 1 0 0 0-1-1zM4 4h8a1 1 0 0 1 1 1v1H3V5a1 1 0 0 1 1-1zm-1 3h10v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7z"/>
                        <path d="M5.5 9a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1H9a.5.5 0 0 1-.5-.5zm-3 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1H9a.5.5 0 0 1-.5-.5z"/>
                    </svg>
                </span>
                <span class="nav-label">Stock Opname</span>
            </Link>

            <template v-if="hasAnyPermission(['ai_assistant.use'])">
                <div class="nav-section-label">AI ASSISTANT</div>

                <Link href="/apps/ai-assistant" class="nav-item" :class="{ active: $page.url.startsWith('/apps/ai-assistant') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M6 12.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5ZM3 8.062C3 6.76 4.235 5.765 5.53 5.886a26.58 26.58 0 0 0 4.94 0C11.765 5.765 13 6.76 13 8.062v1.157a.933.933 0 0 1-.765.935c-.845.147-2.34.346-4.235.346-1.895 0-3.39-.2-4.235-.346A.933.933 0 0 1 3 9.219V8.062Zm4.542-.827a.25.25 0 0 0-.217.068l-.92.9a24.767 24.767 0 0 1-1.871-.183.25.25 0 0 0-.068.495c.55.076 1.232.149 2.02.193a.25.25 0 0 0 .189-.071l.754-.736.847 1.71a.25.25 0 0 0 .404.062l.932-.97a25.286 25.286 0 0 0 1.922-.188.25.25 0 0 0-.068-.495c-.538.074-1.207.145-1.98.189a.25.25 0 0 0-.166.076l-.754.785-.842-1.7a.25.25 0 0 0-.182-.135Z"/>
                            <path d="M8.5 1.866a1 1 0 1 0-1 0V3h-2A4.5 4.5 0 0 0 1 7.5V8a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1v1a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-1a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1v-.5A4.5 4.5 0 0 0 10.5 3h-2V1.866ZM14 7.5V13a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7.5A3.5 3.5 0 0 1 5.5 4h5A3.5 3.5 0 0 1 14 7.5Z"/>
                        </svg>
                    </span>
                    <span class="nav-label">AI Assistant</span>
                    <span class="nav-badge badge-ai">AI</span>
                </Link>
            </template>

            <div class="nav-section-label">DEPARTEMEN</div>

            <Link v-if="!isDepartmentUser" href="/apps/departments" class="nav-item" :class="{ active: $page.url.startsWith('/apps/departments') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M4 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v11.5h-8V2.5z"/>
                        <path d="M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v14.5a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5V1zm2 0v14h8V1H4z"/>
                    </svg>
                </span>
                <span class="nav-label">Department</span>
            </Link>

            <Link href="/apps/pics" class="nav-item" :class="{ active: $page.url.startsWith('/apps/pics') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                        <path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0h-7z"/>
                    </svg>
                </span>
                <span class="nav-label">Data PIC</span>
            </Link>

            <template v-if="!isDepartmentUser">
                <div class="nav-section-label">USER MANAGEMENT</div>

                <Link href="/apps/roles" class="nav-item" :class="{ active: $page.url.startsWith('/apps/roles') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524z"/>
                            <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </span>
                    <span class="nav-label">Roles</span>
                </Link>

                <Link href="/apps/permissions" class="nav-item" :class="{ active: $page.url.startsWith('/apps/permissions') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10H7.465A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                        </svg>
                    </span>
                    <span class="nav-label">Permissions</span>
                </Link>

                <Link href="/apps/users" class="nav-item" :class="{ active: $page.url.startsWith('/apps/users') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                        </svg>
                    </span>
                    <span class="nav-label">Users</span>
                </Link>
            </template>

        </nav>

        <div class="sidebar-footer">
            <div class="footer-text">MaterialTrack © 2026</div>
        </div>
    </div>
</template>

<script>
import { Link, usePage } from '@inertiajs/vue3';
import logoUrl from '@/assets/logo-materialtrack.png';

export default {
    components: { Link },
    emits: ['toggle'],
    data() {
        return { collapsed: false, logoUrl };
    },
    computed: {
        isDepartmentUser() {
            const page = usePage();
            const roles = page.props.auth?.user?.roles ?? [];
            return roles.includes('department_user');
        }
    },
    methods: {
        toggle() {
            this.collapsed = !this.collapsed;
            this.$emit('toggle', this.collapsed);
        }
    }
}
</script>

<style scoped>
.sidebar {
    width: 260px;
    min-height: 100vh;
    background: linear-gradient(180deg, #0f172a 0%, #0b1120 100%);
    display: flex;
    flex-direction: column;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    z-index: 100;
    transition: width 0.3s ease;
    overflow: hidden;
}

.sidebar-collapsed { width: 70px; }

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    min-height: 70px;
}

.brand-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: white;
    padding: 4px;
}
.brand-logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.brand-name {
    color: white;
    font-size: 16px;
    font-weight: 700;
    white-space: nowrap;
    transition: opacity 0.3s;
}

.sidebar-collapsed .brand-name { opacity: 0; width: 0; }

.sidebar-toggle {
    margin-left: auto;
    background: none;
    border: none;
    color: rgba(255,255,255,0.5);
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    flex-shrink: 0;
    transition: color 0.2s;
}

.sidebar-toggle:hover { color: white; background: rgba(255,255,255,0.08); }

.sidebar-nav {
    flex: 1;
    padding: 12px 0;
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar-nav::-webkit-scrollbar { width: 4px; }
.sidebar-nav::-webkit-scrollbar-track { background: transparent; }
.sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

.nav-section-label {
    font-size: 10px;
    font-weight: 600;
    color: rgba(255,255,255,0.3);
    letter-spacing: 1px;
    padding: 16px 20px 6px;
    white-space: nowrap;
    transition: opacity 0.3s;
}

.sidebar-collapsed .nav-section-label { opacity: 0; }

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    margin: 2px 8px;
    border-radius: 8px;
    color: rgba(255,255,255,0.65);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    white-space: nowrap;
}

.nav-item:hover {
    background: rgba(255,255,255,0.08);
    color: white;
    text-decoration: none;
}

.nav-item.active {
    background: linear-gradient(90deg, #7c3aed 0%, #6d28d9 100%);
    color: white;
    box-shadow: 0 4px 14px rgba(124,58,237,0.4);
}

.nav-icon {
    width: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.nav-label { transition: opacity 0.3s; flex: 1; }
.sidebar-collapsed .nav-label { opacity: 0; width: 0; overflow: hidden; }

.nav-badge {
    font-size: 9px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 20px;
    flex-shrink: 0;
}

.badge-success { background: rgba(34,197,94,0.2); color: #4ade80; }
.badge-danger { background: rgba(239,68,68,0.2); color: #f87171; }
.badge-ai { background: rgba(124,58,237,0.2); color: #a78bfa; }
.sidebar-collapsed .nav-badge { display: none; }

.sidebar-footer {
    padding: 16px;
    border-top: 1px solid rgba(255,255,255,0.08);
}

.footer-text {
    font-size: 11px;
    color: rgba(255,255,255,0.25);
    text-align: center;
    white-space: nowrap;
    transition: opacity 0.3s;
}

.sidebar-collapsed .footer-text { opacity: 0; }
</style>