<template>
    <div>
        <Header></Header>
        
        <div class="page-header">
                <div class="page-title">
                    <h3>Activiteit</h3>
                </div>
                <Nav></Nav>
            </div>

        <div class="main">

            <div class="card fullwidth detail">
                <div class="row content">
                    <div class="column">
                        <p class="text-light">Klant</p>
                        <p>{{activity.customer.companyName}}</p>
                    </div>
                </div>
                <div class="row content">
                    <div class="column">
                        <p class="text-light">Van</p>
                        <p>{{activity.start_time}}</p>
                    </div>
                    <div class="column">
                        <p class="text-light">Van</p>
                        <p>{{activity.start_time}}</p>
                    </div>
                </div>
                <div class="row content">
                    <div class="column">
                        <p class="text-light">Beschrijving</p>
                        <p>{{activity.description}}</p>
                    </div>
                    <div class="column">
                        <p class="text-light">Gebruikte materialen</p>
                        <p>{{activity.used_materials}}</p>
                    </div>
                </div>
            </div>
        </div>
        
    </div>



</template>

<script>

import Header from'../partials/Header';
import BottomBar from'../partials/BottomBar';
import Nav from'../partials/Nav';

import { userService, activityService } from '../../services';

export default {
    components: {
        Header,
        BottomBar,
        Nav
    },
    computed: {
        user () {
            return this.$store.state.authentication.user;
        },
        activity () {
            return this.$store.state.activity.activity;
        },
    },
    created () {
        this.$store.dispatch('activity/getActivity', this.$route.params.id);
    },
    watch: {
        '$route' (to, from) {
            this.$store.dispatch('activity/getActivity', this.$route.params.id);
        }
    },

};
</script>