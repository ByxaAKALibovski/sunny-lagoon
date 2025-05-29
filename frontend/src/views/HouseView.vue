<template>
    <main>
        <section class="house__sec">
            <div class="container">
                <nav class="house__nav">
                    <router-link :to="`/house`" class="btn btn__reverse" :class="{ active: !selectedCategory }">Все дома</router-link>
                    <router-link v-for="category in categories" :key="category.id_category" 
                        :to="`/house?category=${category.id_category}`" 
                        class="btn btn__reverse" 
                        :class="{ active: selectedCategory == category.id_category }">
                        {{ category.title }}
                    </router-link>
                </nav>
                <h2 class="title__page">{{ categoryTitle }}</h2>
                <p v-if="categoryDescription">
                    <span class="span__text title__category">{{ categoryTitle }}</span> - {{ categoryDescription }}
                </p>
                <h3 class="title__sec">Выберете свой {{ categorySubtitle }}</h3>
                <ul class="house__list">
                    <li v-for="house in filteredHouses" :key="house.id_house">
                        <div class="image__block">
                            <img class="active__image" :src="house.activeImage || (house.images && house.images.length ? `https://api.sunny-lagoon.ru/uploads/${house.images[0]}` : '')" alt="house">
                            <div class="list__image">
                                <img v-for="(image, index) in house.images" :key="index" 
                                    :class="{ active: index === house.activeIndex }" 
                                    :src="`https://api.sunny-lagoon.ru/uploads/${image}`" 
                                    alt=""
                                    @click="setActiveImage(house, image, index)">
                            </div>
                        </div>
                        <div class="text__content">
                            <h3 class="title__house">{{ house.title }}</h3>
                            <p class="capacity"><img src="@/assets/media/icons/group.png" alt="group"> {{ house.capacity }} гостя</p>
                            <p class="description" v-html="house.description"></p>
                            <div class="bottom">
                                <router-link :to="`/reservation/house=${house.id_house}`" class="btn">Забронировать</router-link>
                                <p class="price"><span>{{ house.price }}</span> ₽ / чел</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </section>
    </main>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { getCategories, getHouses } from '../services/api'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const categories = ref([])
const houses = ref([])
const selectedCategory = ref(null)
const categoryTitle = ref('Все дома')
const categoryDescription = ref('')
const categorySubtitle = ref('дом')

const filteredHouses = computed(() => {
    if (!selectedCategory.value) {
        return houses.value;
    }
    return houses.value.filter(house => house.id_category == selectedCategory.value);
})

const parseRouteParams = async () => {
    const categoryParam = route.query.category;
    if (categoryParam) {
        selectedCategory.value = parseInt(categoryParam);
        await loadCategoryDetails(selectedCategory.value);
    } else {
        selectedCategory.value = null;
        categoryTitle.value = 'Все дома';
        categoryDescription.value = '';
        categorySubtitle.value = 'дом';
    }
}

const loadCategoryDetails = async (categoryId) => {
    try {
        if (!categories.value || categories.value.length === 0) {
            const response = await getCategories();
            categories.value = response.data.data.categories;
        }
        
        const category = categories.value.find(cat => cat.id_category == categoryId);
        
        if (category) {
            categoryTitle.value = category.title;
            categoryDescription.value = category.description;
            categorySubtitle.value = category.short_title;
        } else {
            console.error('Категория не найдена');
            categoryTitle.value = 'Все дома';
            categoryDescription.value = '';
            categorySubtitle.value = 'дом';
        }
    } catch (error) {
        console.error('Ошибка при загрузке данных о категории:', error);
    }
}

const setActiveImage = (house, image, index) => {
    house.activeImage = `https://api.sunny-lagoon.ru/uploads/${image}`;
    house.activeIndex = index;
}

const initializeHouseImages = () => {
    houses.value.forEach(house => {
        if (house.images && house.images.length) {
            house.activeImage = `https://api.sunny-lagoon.ru/uploads/${house.images[0]}`;
            house.activeIndex = 0;
        }
    });
}

onMounted(async () => {
    try {
        const [categoriesResponse, housesResponse] = await Promise.all([
            getCategories(),
            getHouses()
        ]);
        
        categories.value = categoriesResponse.data.data.categories;
        houses.value = housesResponse.data.data.homes;
        
        initializeHouseImages();
        await parseRouteParams();
    } catch (error) {
        console.error('Ошибка при загрузке данных:', error);
    }
})

watch(() => route.query.category, async () => {
    await parseRouteParams();
})
</script>