<template>
    <header>
        <div class="container">
            <svg width="869.000000" height="59.000000" viewBox="0 0 869 59" fill="none"
                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <desc>
                    Created with Pixso.
                </desc>
                <defs />
                <path id="Прямоугольник 1"
                    d="M0 0L869 0.29L838.86 53.9C837.09 57.05 833.75 59 830.14 59L39.47 59C35.88 59 32.57 57.07 30.79 53.95L0 0Z"
                    fill="#0FB1C3" fill-opacity="1.000000" fill-rule="evenodd" />
            </svg>
            <nav class="header__nav">
                <RouterLink to="/about">О нас</RouterLink>
                <RouterLink to="/category">Домики</RouterLink>
                <RouterLink to="/contact">Контакты</RouterLink>
                <RouterLink to="/gaiety">Развлечения</RouterLink>
                <RouterLink to="/promotion">Акции</RouterLink>
                <RouterLink to="/reviews">Отзывы</RouterLink>
            </nav>
            <div class="circ">
                <RouterLink to="/category" class="logo">
                    <img src="@/assets/media/icons/logo.png" alt="logo">
                </RouterLink>
            </div>
        </div>
    </header>
</template>