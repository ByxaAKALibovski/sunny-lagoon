<template>
  <header class="index__header">
    <div class="container">
      <nav>
        <RouterLink to="/about">О нас</RouterLink>
        <RouterLink to="/category">Домики</RouterLink>
        <RouterLink to="/contact">Контакты</RouterLink>
        <RouterLink to="/gaiety">Развлечения</RouterLink>
        <RouterLink to="/promotion">Акции</RouterLink>
        <RouterLink to="/reviews">Отзывы</RouterLink>
      </nav>
      <img src="@/assets/media/image/index.jpg" alt="back">
      <div class="content">
        <h1>Солнечная Лагуна</h1>
        <p>Отдых, который вы запомните навсегда!</p>
        <RouterLink to="/reservation" class="btn btn__light">Забронировать домик</RouterLink>
      </div>
    </div>
  </header>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script> 