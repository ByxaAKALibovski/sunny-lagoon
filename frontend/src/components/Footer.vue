<template>
    <footer>
            <div class="container">
                <RouterLink to="/category" class="logo">
                    <img src="@/assets/media/icons/logo-color.png" alt="logo">
                    Солнечная Лагуна
                </RouterLink>
                <nav class="footer__nav">
                    <div class="column">
                        <RouterLink to="/about">О нас</RouterLink>
                        <RouterLink to="/house">Домики</RouterLink>
                    </div>
                    <div class="column">
                        <RouterLink to="/contact">Контакты</RouterLink>
                        <RouterLink to="/gaiety">Развлечения</RouterLink>
                    </div>
                    <div class="column">
                        <RouterLink to="/promotion">Акции</RouterLink>
                        <RouterLink to="/reviews">Отзывы</RouterLink>
                    </div>
                </nav>
                <address>
                    <div class="column">
                        <a href="tel: + 7 (930) 333-47-56">+ 7 (930) 333-47-56</a>
                        <a href="mailto: info@Sunny-Lagoon.ru">info@Sunny-Lagoon.ru</a>
                    </div>
                    <div class="column">
                        <p>Пензенская область,
                            пос. Возрождение, Отрадная, 1</p>
                    </div>
                </address>
            </div>
        </footer>
</template>