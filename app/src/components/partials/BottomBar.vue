<template>
    <div class="bottom-bar">
        <ul>
            <router-link class="tab" :to="{ name: 'home'}">Activiteiten</router-link>
            <router-link class="tab" :to="{ name: 'settings'}">Instellingen</router-link>
        </ul>
    </div>
</template>

<script>
export default {
    created () {

    },
    computed: {

    },
    methods: {

    }
};
</script>