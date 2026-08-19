<template>
    <div class="sidebar-overlay" v-if="mobileOpen" @click="toggleMobile"></div>
    <div class="sidebar" :class="{ 'sidebar-collapsed': collapsed, 'sidebar-mobile-open': mobileOpen }">
        <div class="sidebar-brand">
            <img :src="logoUrl" alt="MaterialTrack" class="brand-logo" />
            <button class="sidebar-collapse-btn" @click="toggle" :title="collapsed ? 'Expand' : 'Collapse'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 3.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13a.5.5 0 0 1-.5-.5zm0 4a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13a.5.5 0 0 1-.5-.5zm0 4a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13a.5.5 0 0 1-.5-.5z"/>
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
                <span class="nav-label">{{ $t('sidebar.dashboard') }}</span>
            </Link>

            <template v-if="!isDepartmentUser">
                <div class="nav-section-label">MASTER DATA</div>

                <Link href="/apps/suppliers" class="nav-item" :class="{ active: $page.url.startsWith('/apps/suppliers') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9a1.5 1.5 0 0 1 0 3h-9A1.5 1.5 0 0 1 0 3.5zm11 1a1.5 1.5 0 0 1 1.5-1.5h1A1.5 1.5 0 0 1 15 4.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5v-1zM1.5 5a.5.5 0 0 0-.5.5v6a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-6a.5.5 0 0 0-.5-.5h-9zm9.5 1v5h-9v-5h9zM1 10a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-1a1 1 0 0 0-1-1H1zm11 0a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-1a1 1 0 0 0-1-1h-1z"/>
                        </svg>
                    </span>
                    <span class="nav-label">{{ $t('sidebar.suppliers') }}</span>
                </Link>

                <Link href="/apps/categories" class="nav-item" :class="{ active: $page.url.startsWith('/apps/categories') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4H2.19zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707z"/>
                        </svg>
                    </span>
                    <span class="nav-label">{{ $t('sidebar.categories') }}</span>
                </Link>
            </template>

            <Link href="/apps/items" class="nav-item" :class="{ active: $page.url.startsWith('/apps/items') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zm-7.5 10.522V6.84L1 4.24v7.922l6.5 2.6z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.items') }}</span>
            </Link>

            <Link v-if="!isDepartmentUser" href="/apps/locations" class="nav-item" :class="{ active: $page.url.startsWith('/apps/locations') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z"/>
                        <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.locations') }}</span>
            </Link>

            <Link v-if="!isDepartmentUser" href="/apps/warehouses" class="nav-item" :class="{ active: $page.url.startsWith('/apps/warehouses') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zm-7.5 10.522V6.84L1 4.24v7.922l6.5 2.6z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.warehouses') }}</span>
            </Link>

            <Link v-if="!isDepartmentUser" href="/apps/locations/map" class="nav-item" :class="{ active: $page.url === '/apps/locations/map' }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 0a5 5 0 0 0-5 5c0 3.5 5 10.5 5 10.5s5-7 5-10.5A5 5 0 0 0 8 0zm0 7a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.warehouse_map') }}</span>
            </Link>

            <div class="nav-section-label">TRANSAKSI STOK</div>

            <Link v-if="!isDepartmentUser" href="/apps/stock-ins" class="nav-item" :class="{ active: $page.url.startsWith('/apps/stock-ins') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm10.096 3.146a.5.5 0 0 0-.707-.708L5.5 8.293V5.5a.5.5 0 0 0-1 0v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1H6.207l3.889-3.854z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.stock_in') }}</span>
                <span class="nav-badge badge-success">IN</span>
            </Link>

            <Link href="/apps/stock-outs" class="nav-item" :class="{ active: $page.url.startsWith('/apps/stock-outs') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm5.904 8.854a.5.5 0 0 0 .707.708L10.5 7.707V10.5a.5.5 0 0 0 1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 0 0 1h2.793l-3.889 3.854z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.stock_out') }}</span>
                <span class="nav-badge badge-danger">OUT</span>
            </Link>

            <Link href="/apps/stock-transfers" class="nav-item" :class="{ active: $page.url.startsWith('/apps/stock-transfers') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1 11.5a.5.5 0 0 0 .5.5h11.793l-3.147 3.146a.5.5 0 0 0 .708.708l4-4a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 11H1.5a.5.5 0 0 0-.5.5zm14-7a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 0 1 .708.708L2.707 4H14.5a.5.5 0 0 1 .5.5z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.stock_transfer') }}</span>
            </Link>

            <Link href="/apps/stock-reservations" class="nav-item" :class="{ active: $page.url.startsWith('/apps/stock-reservations') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1H4z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.stock_reservations') }}</span>
            </Link>

            <Link href="/apps/stock-returns" class="nav-item" :class="{ active: $page.url.startsWith('/apps/stock-returns') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.stock_returns') }}</span>
            </Link>

            <Link v-if="!isDepartmentUser" href="/apps/bulk-stock" class="nav-item" :class="{ active: $page.url.startsWith('/apps/bulk-stock') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zm8 0A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm-8 8A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm8 0A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.bulk_stock') }}</span>
            </Link>

            <Link v-if="!isDepartmentUser" href="/apps/stock-movements" class="nav-item" :class="{ active: $page.url.startsWith('/apps/stock-movements') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0v1z"/>
                        <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.stock_movements') }}</span>
            </Link>

            <Link v-if="!isDepartmentUser" href="/apps/stock-opnames" class="nav-item" :class="{ active: $page.url.startsWith('/apps/stock-opnames') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M6 1a1 1 0 0 0-1 1v1H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-1V2a1 1 0 1 0-2 0v1H7V2a1 1 0 0 0-1-1zM4 4h8a1 1 0 0 1 1 1v1H3V5a1 1 0 0 1 1-1zm-1 3h10v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7z"/>
                        <path d="M5.5 9a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1H9a.5.5 0 0 1-.5-.5zm-3 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1H9a.5.5 0 0 1-.5-.5z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.stock_opname') }}</span>
            </Link>

            <Link v-if="hasAnyPermission(['stock_adjustments.index'])" href="/apps/stock-adjustments" class="nav-item" :class="{ active: $page.url.startsWith('/apps/stock-adjustments') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5.5 1a.5.5 0 0 1 .5.5V3h4V1.5a.5.5 0 0 1 1 0V3h.5A1.5 1.5 0 0 1 13 4.5v7.42A2.58 2.58 0 0 1 10.42 14H5.58A2.58 2.58 0 0 1 3 11.42V4.5A1.5 1.5 0 0 1 4.5 3H5V1.5a.5.5 0 0 1 .5-.5zM12 6H4v5.42A1.58 1.58 0 0 0 5.58 13h4.84A1.58 1.58 0 0 0 12 11.42V6z"/>
                        <path d="M6 8a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3A.5.5 0 0 1 6 8zm0 2a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3A.5.5 0 0 1 6 10z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.stock_adjustments') || 'Penyesuaian Stok' }}</span>
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
                    <span class="nav-label">{{ $t('sidebar.ai_assistant') }}</span>
                    <span class="nav-badge badge-ai">AI</span>
                </Link>
            </template>

            <template v-if="hasAnyPermission(['reports.index'])">
                <div class="nav-section-label">LAPORAN</div>

                <Link href="/apps/reports" class="nav-item" :class="{ active: $page.url.startsWith('/apps/reports') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                        </svg>
                    </span>
                    <span class="nav-label">{{ $t('sidebar.reports') || 'Laporan' }}</span>
                </Link>

                <Link v-if="!isDepartmentUser" href="/apps/custom-reports" class="nav-item" :class="{ active: $page.url.startsWith('/apps/custom-reports') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                            <path d="M3 4h10v1H3V4zm0 3h10v1H3V7zm0 3h7v1H3v-1z"/>
                        </svg>
                    </span>
                    <span class="nav-label">{{ $t('sidebar.custom_reports') }}</span>
                </Link>
            </template>

            <template v-if="hasAnyPermission(['items.index'])">
                <div class="nav-section-label">ANALISIS</div>

                <Link href="/apps/forecasting" class="nav-item" :class="{ active: $page.url.startsWith('/apps/forecasting') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M0 0h1v15h15v1H0V0Zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07Z"/>
                        </svg>
                    </span>
                    <span class="nav-label">{{ $t('sidebar.forecasting') }}</span>
                </Link>
            </template>

            <template v-if="hasAnyPermission(['activity_logs.index'])">
                <div class="nav-section-label">AUDIT</div>

                <Link href="/apps/activity-logs" class="nav-item" :class="{ active: $page.url.startsWith('/apps/activity-logs') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M14.5 2a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-.5.5H2a.5.5 0 0 1-.5-.5V2.5a.5.5 0 0 1 1 0V14h12V2.5a.5.5 0 0 1 .5-.5zM1 3a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H1z"/>
                            <path d="M4 6h8v1H4V6zm0 3h8v1H4V9zm0 3h5v1H4v-1z"/>
                        </svg>
                    </span>
                    <span class="nav-label">{{ $t('sidebar.activity_logs') }}</span>
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
                <span class="nav-label">{{ $t('sidebar.departments') }}</span>
            </Link>

            <Link href="/apps/pics" class="nav-item" :class="{ active: $page.url.startsWith('/apps/pics') }">
                <span class="nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                        <path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0h-7z"/>
                    </svg>
                </span>
                <span class="nav-label">{{ $t('sidebar.pics') }}</span>
            </Link>

            <template v-if="!isDepartmentUser">
                <div class="nav-section-label">USER MANAGEMENT</div>

                <Link v-if="hasAnyPermission(['roles.index'])" href="/apps/roles" class="nav-item" :class="{ active: $page.url.startsWith('/apps/roles') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524z"/>
                            <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </span>
                    <span class="nav-label">{{ $t('sidebar.roles') }}</span>
                </Link>

                <Link v-if="hasAnyPermission(['permissions.index'])" href="/apps/permissions" class="nav-item" :class="{ active: $page.url.startsWith('/apps/permissions') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10H7.465A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                        </svg>
                    </span>
                    <span class="nav-label">{{ $t('sidebar.permissions') }}</span>
                </Link>

                <Link v-if="hasAnyPermission(['users.index'])" href="/apps/users" class="nav-item" :class="{ active: $page.url.startsWith('/apps/users') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                        </svg>
                    </span>
                    <span class="nav-label">{{ $t('sidebar.users') }}</span>
                </Link>

                <Link v-if="hasAnyPermission(['items.index'])" href="/apps/stock-alerts" class="nav-item" :class="{ active: $page.url.startsWith('/apps/stock-alerts') }">
                    <span class="nav-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
                        </svg>
                    </span>
                    <span class="nav-label">{{ $t('sidebar.stock_alerts') }}</span>
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
        return { collapsed: false, mobileOpen: false, logoUrl };
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
        },
        toggleMobile() {
            this.mobileOpen = !this.mobileOpen;
        }
    },
    mounted() {
        window.addEventListener('toggle-mobile-sidebar', this.toggleMobile);
    },
    beforeUnmount() {
        window.removeEventListener('toggle-mobile-sidebar', this.toggleMobile);
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
    justify-content: center;
    padding: 20px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    min-height: 70px;
    position: relative;
}

.brand-logo {
    height: 36px;
    width: auto;
    max-width: 200px;
    object-fit: contain;
    transition: all 0.3s ease;
}

.sidebar-collapsed .brand-logo {
    height: 32px;
    max-width: 32px;
}

.sidebar-collapse-btn {
    position: absolute;
    right: -12px;
    top: 50%;
    transform: translateY(-50%);
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.6);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    z-index: 10;
}

.sidebar-collapse-btn:hover {
    background: #334155;
    color: white;
    border-color: rgba(255,255,255,0.25);
}

.sidebar-collapsed .sidebar-collapse-btn {
    right: -12px;
}

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

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    .sidebar.sidebar-mobile-open {
        transform: translateX(0);
    }
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 99;
    }
}
</style>