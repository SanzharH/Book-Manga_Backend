<?php

return [
    // Стоимость доставки рассчитывается на сервере, фронтенд только показывает её.
    'delivery_price' => (float) env('SHOP_DELIVERY_PRICE', 1500),
    'free_delivery_from' => (float) env('SHOP_FREE_DELIVERY_FROM', 15000),
];
