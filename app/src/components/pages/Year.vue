<template>
    <div>
        <Header></Header>
        
        <div class="page-header">
                <div class="page-title">
                    <h3>Activiteiten</h3>
                </div>
                <Nav></Nav>
            </div>

        <div class="main">
            <div class="activities-options">
                <router-link :to="{ name: 'year', params: { date: lastYearString }}">&lt;</router-link>
                <p>{{year}}</p>
                <router-link :to="{ name: 'year', params: { date: nextYearString }}">&gt;</router-link>
            </div>
            
            <div class="activities">
                <div >
                    <router-link :to="{ name: 'detail', params: { id: activity.id }}" class="activity-bar" v-if="activities.length > 0" v-for="activity in activities" :key="activity.id">
                    <div class="activity-bar-title">
                        <p>{{activity.description}}</p>
                    </div>
                    <div class="activity-bar-content">
                        <div>
                            <p class="text-light">klant</p>
                            <p>{{activity.customer.companyName}}</p>
                        </div>
                        <div>
                            <p class="text-light">van</p>
                            <p>{{activity.start_time}}</p>
                        </div>
                        <div>
                            <p class="text-light">tot</p>
                            <p>{{activity.end_time}}</p>
                        </div>
                    </div>
                </router-link>
                </div> 
            </div>

            <div class="card fullwidth totalHours" v-if="activities.length > 0">
                <div class="row">
                    <p>Totaal uren dit jaar: </p>
                </div>
                <div class="row">
                    <p class="card-text-large">{{totalHours}} uur</p>
                </div>
            </div>
            <div class="message" v-if="activities.length <= 0"><p>U heeft geen activiteiten voor deze periode</p></div>
        </div>


        <a href="/activities/add"><div class="add-button"><p>+</p></div></a>

        
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
        activities () {
            return this.$store.state.activity.activities;
        },
        year () {
            return this.$store.state.activity.year;
        },   
        totalHours () {
            return this.$store.state.activity.totalHours;
        },
        nextYearString() {
            let thisDate;
            if(this.$route.params.date){
                thisDate = new Date(this.$route.params.date);
            }else {
                thisDate = new Date();
            }
            thisDate.setFullYear( thisDate.getFullYear() + 1);
            return thisDate.toISOString().substring(0, 10);
        },
        lastYearString() {
            let thisDate;
            if(this.$route.params.date){
                thisDate = new Date(this.$route.params.date);
            }else {
                thisDate = new Date();
            }
            thisDate.setFullYear( thisDate.getFullYear() - 1);
            return thisDate.toISOString().substring(0, 10);
        }
    },
    created () {
        this.$store.dispatch('activity/getYearActivities', this.$route.params.date);
    },
    watch: {
        '$route' (to, from) {
            this.$store.dispatch('activity/getYearActivities', this.$route.params.date)
        }
    },

};
</script>