<template>
    <main>
        <section class="category__sec">
            <div class="container">
                <h2 class="title__page">
                    У нас вы найдете пространства <span>с уникальными стилями</span>
                </h2>
                <p>Мы понимали, что у всех разные вкусы и предпочтения: кто-то ищет уединения в природе, кто-то
                    хочет насладиться современным комфортом, а кто-то мечтает о необычном и экзотическом опыте. </p>
                <p>Выберите свое размещение - экзотический отдых в юрте, уединение в глэмпинге или комфортное
                    размещение в барнхаусе.</p>
                <ul class="category__list">
                    <li v-for="category in categories" :key="category.id_category">
                        <img class="image__link" :src="'https://api.sunny-lagoon.ru/uploads/' + category.image_link" alt="category">
                        <h3 class="title">{{ category.title }}</h3>
                        <p class="capacity">Вместимость на {{ category.capacity }} гостей</p>
                        <p class="prev__text">{{ category.prev_text }}</p>
                        <router-link :to="`/house?category=${category.id_category}`" class="btn">Выбрать {{ category.short_title }}</router-link>
                    </li>
                </ul>
            </div>
        </section>
    </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getCategories } from '../services/api'

const categories = ref([])

onMounted(async () => {
    const response = await getCategories()
    categories.value = response.data.data.categories
})
</script>
