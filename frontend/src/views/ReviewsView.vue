<template>
    <main>
        <div class="popup" :class="{ active: isFormActive }" data-popup="add_reviews">
            <div class="over" @click="closeForm"></div>
            <div class="main">
                <h2 class="title__page">Оставить отзыв</h2>
                <form @submit.prevent="submitReview">
                    <div class="input__block">
                        <label for="name">Ваше имя и фамилия</label>
                        <input type="text" class="input__cust" id="name" v-model="newReview.name" required>
                    </div>
                    <div class="input__block">
                        <label for="reviews">Текст отзыва</label>
                        <textarea name="reviews" id="reviews" class="input__cust" v-model="newReview.text" required></textarea>
                    </div>
                    <input type="submit" class="btn" value="Отправить отзыв">
                </form>
            </div>
        </div>
        <section class="reviews__sec">
            <div class="container">
                <h2 class="title__page">Отзывы наших гостей</h2>
                <p>Мы гордимся тем, что наши гости остаются довольны своим временем у нас! Мы ценим каждое ваше
                    мнение и стремимся сделать ваше пребывание еще лучше. Ваши отзывы помогают нам расти и
                    развиваться, чтобы каждый гость чувствовал себя особенным. Делитесь своими впечатлениями, и
                    давайте вместе создавать незабываемые моменты!</p>
                <button class="btn" @click="openForm">Оставить отзыв</button>
                <ul class="reviews__list">
                    <li v-for="review in reviews" :key="review.id_reviews">
                        <div class="row">
                            <p class="name">{{ review.name }}</p>
                            <p class="date">{{ formatDate(review.created_at) }}</p>
                        </div>
                        <p class="reviews">{{ review.text }}</p>
                    </li>
                </ul>
            </div>
        </section>
    </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getReviews, createReview } from '../services/api'

// Состояние отзывов
const reviews = ref([])
const isFormActive = ref(false)
const newReview = ref({
    name: '',
    text: ''
})

// Получение всех отзывов
const loadReviews = async () => {
    try {
        const response = await getReviews()
        reviews.value = response.data.data.reviews
    } catch (error) {
        console.error('Ошибка при загрузке отзывов:', error)
    }
}

// Форматирование даты
const formatDate = (dateString) => {
    const date = new Date(dateString)
    const day = date.getDate().toString().padStart(2, '0')
    const month = (date.getMonth() + 1).toString().padStart(2, '0')
    const year = date.getFullYear()
    return `${day}.${month}.${year}`
}

// Управление формой
const openForm = () => {
    isFormActive.value = true
}

const closeForm = () => {
    isFormActive.value = false
    // Сброс формы
    newReview.value = {
        name: '',
        text: ''
    }
}

// Отправка отзыва
const submitReview = async () => {
    try {
        const response = await createReview(newReview.value)
        
        // Добавляем новый отзыв в начало списка
        if (response.data.status === 'success') {
            const newReviewData = response.data.data.review
            reviews.value.unshift(newReviewData)
            
            // Закрытие формы после успешной отправки
            closeForm()
        }
    } catch (error) {
        console.error('Ошибка при отправке отзыва:', error)
        alert('Произошла ошибка при отправке отзыва. Пожалуйста, попробуйте позже.')
    }
}

// Загрузка отзывов при монтировании компонента
onMounted(loadReviews)
</script>