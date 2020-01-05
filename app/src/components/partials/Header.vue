<template>
<div>
    <div class="header">
        <div class="header-left">
            <h3>ARTE-TECH</h3>
        </div>
        
        <div class="header-right">
            <p>ingelogd als {{ user.firstname }} {{ user.name }}</p>
            <p><router-link to="/login">Uitloggen</router-link></p>
        </div>
        
    </div>
</div>
</template>

<script>
export default {
    created () {

    },
    computed: {
        user () {
            return this.$store.state.authentication.user;
        },
        users () {

        }
    },
    methods: {

    }
};
</script>