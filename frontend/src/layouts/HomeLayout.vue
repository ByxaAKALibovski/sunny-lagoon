<template>
  <div class="wrapper home-wrapper">
    <HeaderHome />
    <RouterView />
  </div>
</template>

<script setup>
import { RouterView } from 'vue-router'
import HeaderHome from '../components/HeaderHome.vue'
</script> 