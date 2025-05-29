<template>
    <main>
        <section class="gaiety__sec">
            <div class="container">
                <h2 class="title__page">Чем у нас можно заняться</h2>
                <p>У нас вы найдете множество вариантов для активного отдыха и приятного времяпрепровождения.
                    Приходите
                    к нам и выберите то, что вам по душе! Мы гарантируем, что ваше время с нами будет незабываемым!
                </p>
                <h3 class="title__sec">Дополнительные услуги</h3>
                <ul>
                    <li v-for="gaiety in gaieties" :key="gaiety.id_gaieties">
                        <img class="image__link" :src="'https://api.sunny-lagoon.ru/uploads/' + gaiety.image_link" alt="gaieties">
                        <div class="text__content">
                            <h3>{{ gaiety.title }}</h3>
                            <p>{{ gaiety.description }}
                            </p>
                            <router-link to="/services" class="btn">Стоимость услуг</router-link>
                        </div>
                    </li>
                </ul>
            </div>
        </section>
    </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getGaieties } from '../services/api'

const gaieties = ref([])

onMounted(async () => {
    const response = await getGaieties()
    gaieties.value = response.data.data.gaieties
})
</script>