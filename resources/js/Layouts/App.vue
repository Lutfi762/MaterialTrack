<template>
  <div class="c-app">
    <!-- Sidebar -->
    <div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">
      <div class="c-sidebar-brand d-lg-down-none" style="background: #894b9d;">
        <span class="ml-2 font-weight-bold text-white fs-5">APLIKASI KASIR</span>
      </div>

      <!-- Component Sidebar -->
      <Sidebar />
    </div>

    <div class="c-wrapper c-fixed-components">
      <!-- Component Header -->
      <Header />

      <!-- Main Body Content -->
      <div class="c-body">
        <slot />

        <!-- Footer -->
        <footer class="c-footer">
          <div><strong>APLIKASI KASIR</strong> &copy; 2022 - SantriKoding.com.</div>
        </footer>
      </div>
    </div>
  </div>
</template>

<script>
import Header from '../Components/Header.vue';
import Sidebar from '../Components/Sidebar.vue';

export default {
  components: {
    Header,
    Sidebar
  }
}
</script>