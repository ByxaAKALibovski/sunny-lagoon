<template>
    <main>
        <section class="promotion__sec">
            <div class="container">
                <h2 class="title__page">Больше отдыхаешь - меньше платишь</h2>
                <p>Мы знаем, как важен качественный отдых, и хотим сделать его доступнее для вас. Воспользуйтесь
                    нашими уникальными предложениями и погрузитесь в атмосферу уюта и комфорта в наших Скандинавских
                    глэмпингах, уютных юртах и стильных барнхаусах.</p>
                <h3 class="title__sec">Спецпреложения</h3>
                <ul>
                    <li v-for="promotion in promotions" :key="promotion.id_promotion">
                        <img :src="'https://api.sunny-lagoon.ru/uploads/' + promotion.image_link" alt="">
                        <div class="text__content">
                            <h3>{{ promotion.title }}</h3>
                            <p>{{ promotion.description }}</p>
                        </div>
                    </li>
                </ul>
            </div>
        </section>
    </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getPromotions } from '../services/api'

const promotions = ref([])

onMounted(async () => {
    const response = await getPromotions()
    promotions.value = response.data.data.promotions
})
</script>
