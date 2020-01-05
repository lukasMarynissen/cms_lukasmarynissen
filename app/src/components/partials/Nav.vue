<template>
    <div class="tabs">
        <ul>
            <router-link class="tab" :to="{ name: 'home'}">Week</router-link>
            <li class="divider"></li>
            <router-link class="tab" :to="{ name: 'month'}">Maand</router-link>
            <li class="divider"></li>
            <router-link class="tab" :to="{ name: 'year'}">Jaar</router-link>

        </ul>
    </div>
</template>