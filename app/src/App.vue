<template>
    <div class="container">
        <div class="row">
            <div class="col-sm-6 offset-sm-3">
                <router-view></router-view>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'app',
    computed: {

    },
    watch:{
        $route (){

        }
    } 
};
</script>